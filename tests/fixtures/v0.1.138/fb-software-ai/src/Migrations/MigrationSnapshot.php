<?php
/**
 * Size-bounded snapshot for the architecture baseline migration.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

final class MigrationSnapshot {
    /** @var array */
    private $schema_snapshot;

    /** @var string */
    private $settings_checksum;

    public function __construct(array $schema_snapshot, $settings_checksum) {
        $this->schema_snapshot = $schema_snapshot;
        $this->settings_checksum = (string) $settings_checksum;
    }

    /** @return array */
    public function schema_snapshot() {
        return $this->schema_snapshot;
    }

    /** @return string */
    public function settings_checksum() {
        return $this->settings_checksum;
    }
}
