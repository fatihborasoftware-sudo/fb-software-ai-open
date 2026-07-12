<?php
/**
 * Small explicit service container used by the modular architecture.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

use RuntimeException;

final class Container {
    /** @var array<string,mixed> */
    private $entries = array();

    /** @var array<string,callable> */
    private $factories = array();

    /** @var array<string,bool> */
    private $resolving = array();

    /**
     * Store a concrete service value.
     *
     * @param string $id Service identifier.
     * @param mixed  $value Service value.
     * @return void
     */
    public function set($id, $value) {
        $id = $this->normalize_id($id);
        $this->entries[$id] = $value;
        unset($this->factories[$id]);
    }

    /**
     * Store a shared service factory.
     *
     * @param string   $id Service identifier.
     * @param callable $factory Factory receiving this container.
     * @return void
     */
    public function factory($id, callable $factory) {
        $id = $this->normalize_id($id);
        $this->factories[$id] = $factory;
        unset($this->entries[$id]);
    }

    /**
     * Determine whether a service is registered.
     *
     * @param string $id Service identifier.
     * @return bool
     */
    public function has($id) {
        $id = (string) $id;
        return array_key_exists($id, $this->entries) || array_key_exists($id, $this->factories);
    }

    /**
     * Resolve a service.
     *
     * @param string $id Service identifier.
     * @return mixed
     * @throws RuntimeException When the service is missing or circular.
     */
    public function get($id) {
        $id = $this->normalize_id($id);

        if (array_key_exists($id, $this->entries)) {
            return $this->entries[$id];
        }

        if (!array_key_exists($id, $this->factories)) {
            throw new RuntimeException('Unknown FB Software AI service: ' . $id);
        }

        if (!empty($this->resolving[$id])) {
            throw new RuntimeException('Circular FB Software AI service dependency: ' . $id);
        }

        $this->resolving[$id] = true;
        try {
            $service = call_user_func($this->factories[$id], $this);
            $this->entries[$id] = $service;
            unset($this->factories[$id]);
        } finally {
            unset($this->resolving[$id]);
        }

        return $this->entries[$id];
    }

    /**
     * @param string $id Raw service identifier.
     * @return string
     * @throws RuntimeException When the identifier is empty.
     */
    private function normalize_id($id) {
        $id = trim((string) $id);
        if ($id === '') {
            throw new RuntimeException('FB Software AI service identifiers cannot be empty.');
        }
        return $id;
    }
}
