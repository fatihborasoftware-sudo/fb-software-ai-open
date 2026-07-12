<?php
/**
 * Contract for internal architecture modules.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

interface ModuleInterface {
    /**
     * Return the stable module identifier.
     *
     * @return string
     */
    public function id();

    /**
     * Register module services and hooks.
     *
     * @param Container $container Service container.
     * @return void
     */
    public function register(Container $container);
}
