<?php
/**
 * Deterministic migration runner for additive architecture slices.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

use Exception;
use FBSoftwareAI\Settings\SettingsRepositoryInterface;
use Throwable;

final class MigrationRunner {
    const LOCK_OPTION = 'fbsa_migration_lock';
    const LOCK_TTL = 300;

    /** @var SchemaVersionRepository */
    private $schemas;

    /** @var SettingsRepositoryInterface */
    private $settings;

    /** @var string */
    private $plugin_version;

    public function __construct(
        SchemaVersionRepository $schemas,
        SettingsRepositoryInterface $settings,
        $plugin_version
    ) {
        $this->schemas = $schemas;
        $this->settings = $settings;
        $this->plugin_version = (string) $plugin_version;
    }

    /**
     * Run ordered migrations once.
     *
     * @param MigrationInterface[] $migrations Ordered migration list.
     * @param string|null          $request_id Optional request identifier.
     * @return MigrationResult
     */
    public function run(array $migrations, $request_id = null) {
        $request_id = $request_id ? (string) $request_id : $this->create_request_id();
        $pending = $this->pending_migrations($migrations);

        if ($pending === array()) {
            return new MigrationResult(MigrationResult::STATUS_NOOP, $request_id, array(), 'Schema is current.');
        }

        if (!$this->acquire_lock($request_id, $pending)) {
            return new MigrationResult(MigrationResult::STATUS_LOCKED, $request_id, array(), 'Another migration request is active.');
        }

        $applied = array();
        $schema_snapshot = $this->schemas->snapshot();
        $settings_checksum = $this->checksum($this->settings->all());
        $snapshot = new MigrationSnapshot($schema_snapshot, $settings_checksum);
        $context = new MigrationContext($this->schemas, $this->settings, $this->plugin_version, $request_id);

        try {
            foreach ($pending as $migration) {
                $current = $this->schemas->version($migration->module());
                if ($current >= $migration->to_version()) {
                    continue;
                }
                if ($current !== $migration->from_version()) {
                    throw new Exception('Unexpected schema version for migration ' . $migration->id() . '.');
                }
                if (!$migration->preflight($context)) {
                    throw new Exception('Migration preflight failed for ' . $migration->id() . '.');
                }
                if (!$migration->up($context)) {
                    throw new Exception('Migration execution failed for ' . $migration->id() . '.');
                }
                if (!$migration->verify($context)) {
                    throw new Exception('Migration verification failed for ' . $migration->id() . '.');
                }

                $this->schemas->advance(
                    $migration->module(),
                    $migration->to_version(),
                    array(
                        'plugin_baseline'     => $this->plugin_version,
                        'last_migration_id'   => $migration->id(),
                        'migration_request_id'=> $request_id,
                        'updated_at_gmt'      => $this->current_gmt_time(),
                    )
                );
                $applied[] = $migration->id();
            }

            return new MigrationResult(MigrationResult::STATUS_APPLIED, $request_id, $applied, 'Plugin migrations applied.');
        } catch (Throwable $error) {
            foreach (array_reverse($pending) as $migration) {
                try {
                    $migration->recover($context, $snapshot);
                } catch (Throwable $recovery_error) {
                    // Repository restoration below is authoritative for Slice 1.
                }
            }
            $this->schemas->restore($snapshot->schema_snapshot());
            return new MigrationResult(MigrationResult::STATUS_FAILED, $request_id, $applied, $this->sanitize_error($error->getMessage()));
        } finally {
            $this->release_lock($request_id);
        }
    }

    /**
     * @param MigrationInterface[] $migrations Candidate migrations.
     * @return MigrationInterface[]
     */
    private function pending_migrations(array $migrations) {
        $valid = array();
        foreach ($migrations as $migration) {
            if (!$migration instanceof MigrationInterface) {
                continue;
            }
            if ($this->schemas->version($migration->module()) < $migration->to_version()) {
                $valid[] = $migration;
            }
        }

        usort($valid, function (MigrationInterface $left, MigrationInterface $right) {
            $left_key = $left->module() . ':' . str_pad((string) $left->to_version(), 10, '0', STR_PAD_LEFT) . ':' . $left->id();
            $right_key = $right->module() . ':' . str_pad((string) $right->to_version(), 10, '0', STR_PAD_LEFT) . ':' . $right->id();
            return strcmp($left_key, $right_key);
        });

        return $valid;
    }

    /**
     * @param string               $request_id Request identifier.
     * @param MigrationInterface[] $migrations Pending migrations.
     * @return bool
     */
    private function acquire_lock($request_id, array $migrations) {
        $existing = get_option(self::LOCK_OPTION, array());
        $now = time();
        if (is_array($existing) && !empty($existing['expires_at']) && (int) $existing['expires_at'] > $now) {
            return false;
        }
        if (is_array($existing) && $existing !== array()) {
            delete_option(self::LOCK_OPTION);
        }

        $ids = array();
        foreach ($migrations as $migration) {
            $ids[] = $migration->id();
        }

        return (bool) add_option(
            self::LOCK_OPTION,
            array(
                'owner'       => $request_id,
                'started_at'  => $now,
                'expires_at'  => $now + self::LOCK_TTL,
                'migrations'  => $ids,
            ),
            '',
            false
        );
    }

    /**
     * @param string $request_id Request identifier.
     * @return void
     */
    private function release_lock($request_id) {
        $lock = get_option(self::LOCK_OPTION, array());
        if (is_array($lock) && isset($lock['owner']) && hash_equals((string) $lock['owner'], (string) $request_id)) {
            delete_option(self::LOCK_OPTION);
        }
    }

    /** @return string */
    private function create_request_id() {
        if (function_exists('wp_generate_uuid4')) {
            return (string) wp_generate_uuid4();
        }
        return uniqid('fbsa-', true);
    }

    /** @return string */
    private function current_gmt_time() {
        if (function_exists('current_time')) {
            return (string) current_time('mysql', true);
        }
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * @param mixed $value Value to hash.
     * @return string
     */
    private function checksum($value) {
        return hash('sha256', serialize($value));
    }

    /**
     * @param string $message Internal exception message.
     * @return string
     */
    private function sanitize_error($message) {
        $message = preg_replace('/[\r\n\t]+/', ' ', (string) $message);
        return trim(substr($message, 0, 500));
    }
}
