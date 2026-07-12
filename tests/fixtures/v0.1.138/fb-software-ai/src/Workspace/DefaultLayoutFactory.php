<?php
/**
 * Default Workspace layout definitions.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class DefaultLayoutFactory {
    const OPTION_KEY = 'fbsa_workspace_default_layouts';
    const SCHEMA_VERSION = 1;

    /** @return array<string,mixed> */
    public static function dashboard_layout() {
        return array(
            'schemaVersion' => self::SCHEMA_VERSION,
            'context'       => 'dashboard',
            'layoutId'      => 'fbsa-default-dashboard-v1',
            'roleTemplate'  => 'administrator',
            'widgets'       => array(
                array('id' => 'fbsa_website_steps_widget', 'visible' => true, 'column' => 3, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                array('id' => 'fbsa_website_settings_widget', 'visible' => true, 'column' => 3, 'order' => 1, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                array('id' => 'fbsa_plugin_setup_widget', 'visible' => true, 'column' => 4, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                array('id' => 'fbsa_help_tutorials_widget', 'visible' => true, 'column' => 4, 'order' => 1, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
            ),
            'updatedAt'     => '2026-07-12 12:00:00',
            'source'        => 'default',
        );
    }

    /** @return array<string,mixed> */
    public static function option_value() {
        return array(
            'schemaVersion' => self::SCHEMA_VERSION,
            'layouts'       => array(
                'dashboard' => self::dashboard_layout(),
            ),
        );
    }
}
