<?php
/**
 * Version values for the architecture foundation.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

final class Version {
    const PLUGIN = '0.1.138';
    const ARCHITECTURE = '1.0';
    const CORE_SCHEMA = 1;
    const WORKSPACE_SCHEMA = 1;

    /**
     * Return the plugin release version.
     *
     * @return string
     */
    public static function plugin() {
        return self::PLUGIN;
    }

    /**
     * Return the architecture contract version.
     *
     * @return string
     */
    public static function architecture() {
        return self::ARCHITECTURE;
    }

    /**
     * Return the current core schema version.
     *
     * @return int
     */
    public static function core_schema() {
        return self::CORE_SCHEMA;
    }

    /**
     * Return the current Workspace schema version.
     *
     * @return int
     */
    public static function workspace_schema() {
        return self::WORKSPACE_SCHEMA;
    }
}
