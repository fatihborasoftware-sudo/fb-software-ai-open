<?php
/**
 * Compatibility bridge retaining the v0.1.136 facade while modules are extracted.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Compatibility;

use FBSoftwareAI\Core\Plugin;

final class LegacyFacadeBridge {
    /** @var Plugin */
    private $plugin;

    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Expose the legacy facade to internal services without changing its behavior.
     *
     * @param object $legacy_facade Existing FBSA_Demo_Plugin singleton.
     * @return void
     */
    public function attach($legacy_facade) {
        $this->plugin->attach_legacy_facade($legacy_facade);
    }
}
