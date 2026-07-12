<?php
/**
 * No-op architecture foundation module.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

final class FoundationModule implements ModuleInterface {
    /**
     * @return string
     */
    public function id() {
        return 'core';
    }

    /**
     * Register diagnostic version values without changing user-visible behavior.
     *
     * @param Container $container Service container.
     * @return void
     */
    public function register(Container $container) {
        $container->set('architecture.version', Version::architecture());
        $container->set('plugin.version', Version::plugin());
        $container->set('schema.core.target', Version::core_schema());
    }
}
