<?php
/**
 * Settings repository contract.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Settings;

interface SettingsRepositoryInterface {
    /**
     * Return the complete settings document.
     *
     * @return array
     */
    public function all();

    /**
     * Read one top-level setting.
     *
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Merge changes while preserving unknown fields.
     *
     * @param array $changes Partial settings document.
     * @return array Updated settings document.
     */
    public function merge(array $changes);
}
