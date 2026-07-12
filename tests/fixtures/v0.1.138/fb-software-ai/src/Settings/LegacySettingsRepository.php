<?php
/**
 * Compatibility repository for the existing fbsa_demo_settings option.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Settings;

final class LegacySettingsRepository implements SettingsRepositoryInterface {
    const OPTION_KEY = 'fbsa_demo_settings';

    /**
     * @return array
     */
    public function all() {
        $settings = get_option(self::OPTION_KEY, array());
        return is_array($settings) ? $settings : array();
    }

    /**
     * @param string $key Setting key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($key, $default = null) {
        $settings = $this->all();
        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    /**
     * @param array $changes Partial settings document.
     * @return array
     */
    public function merge(array $changes) {
        $merged = $this->merge_documents($this->all(), $changes);
        update_option(self::OPTION_KEY, $merged);
        return $merged;
    }

    /**
     * Merge associative documents recursively while replacing list values.
     *
     * @param array $existing Existing document.
     * @param array $changes Changes.
     * @return array
     */
    private function merge_documents(array $existing, array $changes) {
        foreach ($changes as $key => $value) {
            if (
                isset($existing[$key]) &&
                is_array($existing[$key]) &&
                is_array($value) &&
                $this->is_associative($existing[$key]) &&
                $this->is_associative($value)
            ) {
                $existing[$key] = $this->merge_documents($existing[$key], $value);
                continue;
            }
            $existing[$key] = $value;
        }
        return $existing;
    }

    /**
     * @param array $value Array to inspect.
     * @return bool
     */
    private function is_associative(array $value) {
        if ($value === array()) {
            return true;
        }
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
