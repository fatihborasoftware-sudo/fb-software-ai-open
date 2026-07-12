<?php
/**
 * Context shared by one migration request.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

use FBSoftwareAI\Settings\SettingsRepositoryInterface;

final class MigrationContext {
    /** @var SchemaVersionRepository */
    private $schemas;

    /** @var SettingsRepositoryInterface */
    private $settings;

    /** @var string */
    private $plugin_version;

    /** @var string */
    private $request_id;

    /** @var array<string,mixed> */
    private $state = array();

    public function __construct(
        SchemaVersionRepository $schemas,
        SettingsRepositoryInterface $settings,
        $plugin_version,
        $request_id
    ) {
        $this->schemas = $schemas;
        $this->settings = $settings;
        $this->plugin_version = (string) $plugin_version;
        $this->request_id = (string) $request_id;
    }

    /** @return SchemaVersionRepository */
    public function schemas() {
        return $this->schemas;
    }

    /** @return SettingsRepositoryInterface */
    public function settings() {
        return $this->settings;
    }

    /** @return string */
    public function plugin_version() {
        return $this->plugin_version;
    }

    /** @return string */
    public function request_id() {
        return $this->request_id;
    }

    /**
     * @param string $key State key.
     * @param mixed  $value State value.
     * @return void
     */
    public function set($key, $value) {
        $this->state[(string) $key] = $value;
    }

    /**
     * @param string $key State key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    public function get($key, $default = null) {
        return array_key_exists($key, $this->state) ? $this->state[$key] : $default;
    }
}
