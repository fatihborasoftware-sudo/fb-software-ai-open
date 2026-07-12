<?php
/**
 * Ordered registry for internal modules.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

use RuntimeException;

final class ModuleRegistry {
    /** @var array<string,ModuleInterface> */
    private $modules = array();

    /** @var array<string,bool> */
    private $registered = array();

    /**
     * Add a module to the registry.
     *
     * @param ModuleInterface $module Module instance.
     * @return void
     * @throws RuntimeException When an identifier is invalid or duplicated.
     */
    public function add(ModuleInterface $module) {
        $id = trim((string) $module->id());
        if ($id === '') {
            throw new RuntimeException('FB Software AI module identifiers cannot be empty.');
        }
        if (isset($this->modules[$id])) {
            throw new RuntimeException('Duplicate FB Software AI module: ' . $id);
        }
        $this->modules[$id] = $module;
    }

    /**
     * Register every module once in insertion order.
     *
     * @param Container $container Service container.
     * @return void
     */
    public function register_all(Container $container) {
        foreach ($this->modules as $id => $module) {
            if (!empty($this->registered[$id])) {
                continue;
            }
            $module->register($container);
            $this->registered[$id] = true;
        }
    }

    /**
     * Return stable registered module identifiers.
     *
     * @return string[]
     */
    public function ids() {
        return array_keys($this->modules);
    }

    /**
     * Determine whether a module is present.
     *
     * @param string $id Module identifier.
     * @return bool
     */
    public function has($id) {
        return isset($this->modules[(string) $id]);
    }
}
