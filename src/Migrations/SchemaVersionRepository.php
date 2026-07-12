<?php
/**
 * Repository for modular schema versions.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

final class SchemaVersionRepository {
    const OPTION_KEY = 'fbsa_schema_versions';
    const MISSING_VALUE = '__fbsa_schema_versions_missing__';

    /**
     * Determine whether the schema map exists.
     *
     * @return bool
     */
    public function exists() {
        return get_option(self::OPTION_KEY, self::MISSING_VALUE) !== self::MISSING_VALUE;
    }

    /**
     * Return the schema map.
     *
     * @return array
     */
    public function all() {
        $value = get_option(self::OPTION_KEY, array());
        return is_array($value) ? $value : array();
    }

    /**
     * Return one module schema version.
     *
     * @param string $module Module identifier.
     * @return int
     */
    public function version($module) {
        $map = $this->all();
        return isset($map[$module]) ? max(0, (int) $map[$module]) : 0;
    }

    /**
     * Advance one module schema version and record migration metadata.
     * Unknown module values remain untouched.
     *
     * @param string $module Module identifier.
     * @param int    $version Target version.
     * @param array  $metadata Migration metadata.
     * @return array Updated map.
     */
    public function advance($module, $version, array $metadata) {
        $map = $this->all();
        $module = (string) $module;
        $version = max(0, (int) $version);
        $current = isset($map[$module]) ? (int) $map[$module] : 0;

        if ($version < $current) {
            $version = $current;
        }

        $map[$module] = $version;
        $existing_meta = isset($map['_meta']) && is_array($map['_meta']) ? $map['_meta'] : array();
        $map['_meta'] = array_merge($existing_meta, $metadata);
        update_option(self::OPTION_KEY, $map, false);
        return $map;
    }

    /**
     * Capture the current option for rollback.
     *
     * @return array{exists:bool,value:array}
     */
    public function snapshot() {
        return array(
            'exists' => $this->exists(),
            'value'  => $this->all(),
        );
    }

    /**
     * Restore a previous repository snapshot.
     *
     * @param array $snapshot Snapshot from snapshot().
     * @return void
     */
    public function restore(array $snapshot) {
        if (!empty($snapshot['exists'])) {
            $value = isset($snapshot['value']) && is_array($snapshot['value']) ? $snapshot['value'] : array();
            update_option(self::OPTION_KEY, $value, false);
            return;
        }
        delete_option(self::OPTION_KEY);
    }
}
