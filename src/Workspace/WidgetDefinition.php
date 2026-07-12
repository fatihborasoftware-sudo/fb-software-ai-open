<?php
/**
 * Immutable widget definition used by the Workspace registry.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use InvalidArgumentException;

final class WidgetDefinition {
    const SCHEMA_VERSION = 1;

    /** @var array<string,mixed> */
    private $data;

    /**
     * @param array<string,mixed> $definition Widget definition data.
     */
    public function __construct(array $definition) {
        $defaults = array(
            'schemaVersion'   => self::SCHEMA_VERSION,
            'id'              => '',
            'version'         => '1.0.0',
            'title'           => '',
            'description'     => '',
            'capability'      => 'manage_options',
            'renderer'        => null,
            'dataProvider'    => null,
            'assetHandles'    => array(),
            'contexts'        => array('dashboard'),
            'defaultPlacement'=> array('context' => 'normal', 'priority' => 'default', 'order' => 0),
            'size'            => array(),
            'removable'       => true,
            'collapsible'     => true,
            'refreshPolicy'   => 'screen_load',
            'settingsSchema'  => array(),
        );

        $definition = array_merge($defaults, $definition);
        $this->validate($definition);
        $definition['contexts'] = array_values(array_unique(array_map('strval', $definition['contexts'])));
        $definition['assetHandles'] = array_values(array_unique(array_map('strval', $definition['assetHandles'])));
        $definition['defaultPlacement'] = array_merge($defaults['defaultPlacement'], $definition['defaultPlacement']);
        $this->data = $definition;
    }

    /** @return string */
    public function id() { return (string) $this->data['id']; }

    /** @return string */
    public function version() { return (string) $this->data['version']; }

    /** @return string */
    public function title() { return (string) $this->data['title']; }

    /** @return string */
    public function description() { return (string) $this->data['description']; }

    /** @return string */
    public function capability() { return (string) $this->data['capability']; }

    /** @return mixed */
    public function renderer() { return $this->data['renderer']; }

    /** @return mixed */
    public function data_provider() { return $this->data['dataProvider']; }

    /** @return string[] */
    public function asset_handles() { return $this->data['assetHandles']; }

    /** @return string[] */
    public function contexts() { return $this->data['contexts']; }

    /** @return array<string,mixed> */
    public function default_placement() { return $this->data['defaultPlacement']; }

    /** @return array<string,mixed> */
    public function size() { return $this->data['size']; }

    /** @return bool */
    public function removable() { return (bool) $this->data['removable']; }

    /** @return bool */
    public function collapsible() { return (bool) $this->data['collapsible']; }

    /** @return string */
    public function refresh_policy() { return (string) $this->data['refreshPolicy']; }

    /** @return array<string,mixed> */
    public function settings_schema() { return $this->data['settingsSchema']; }

    /**
     * Return a copy with presentation-only fields updated.
     * Security-sensitive fields such as capability and renderer are preserved.
     *
     * @param array<string,mixed> $presentation Filtered presentation values.
     * @return self
     */
    public function with_presentation(array $presentation) {
        $data = $this->data;
        foreach (array('title', 'description', 'defaultPlacement', 'size', 'removable', 'collapsible') as $field) {
            if (array_key_exists($field, $presentation)) {
                $data[$field] = $presentation[$field];
            }
        }
        return new self($data);
    }

    /**
     * Return serializable metadata. Renderer callables are represented safely.
     *
     * @return array<string,mixed>
     */
    public function to_array() {
        $data = $this->data;
        if (is_callable($data['renderer']) && !is_string($data['renderer'])) {
            $data['renderer'] = 'callable';
        }
        if (is_callable($data['dataProvider']) && !is_string($data['dataProvider'])) {
            $data['dataProvider'] = 'callable';
        }
        return $data;
    }

    /**
     * @param array<string,mixed> $definition Definition candidate.
     * @return void
     */
    private function validate(array $definition) {
        if ((int) $definition['schemaVersion'] !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Unsupported FB Software AI widget schema version.');
        }
        if (!is_string($definition['id']) || !preg_match('/^[a-z][a-z0-9_.-]{2,190}$/', $definition['id'])) {
            throw new InvalidArgumentException('Invalid FB Software AI widget ID.');
        }
        if (trim((string) $definition['version']) === '') {
            throw new InvalidArgumentException('FB Software AI widget version cannot be empty.');
        }
        if (trim((string) $definition['title']) === '') {
            throw new InvalidArgumentException('FB Software AI widget title cannot be empty.');
        }
        if (trim((string) $definition['capability']) === '') {
            throw new InvalidArgumentException('FB Software AI widget capability cannot be empty.');
        }
        if ((!is_string($definition['renderer']) && !is_callable($definition['renderer']))
            || (is_string($definition['renderer']) && trim($definition['renderer']) === '')) {
            throw new InvalidArgumentException('FB Software AI widget renderer must be a service ID, method name, or callable.');
        }
        if (!is_array($definition['contexts']) || $definition['contexts'] === array()) {
            throw new InvalidArgumentException('FB Software AI widget must support at least one context.');
        }
        $allowed_contexts = array('dashboard', 'workspace', 'frontend', 'customizer');
        foreach ($definition['contexts'] as $context) {
            if (!in_array((string) $context, $allowed_contexts, true)) {
                throw new InvalidArgumentException('Unsupported FB Software AI widget context.');
            }
        }
        if (!is_array($definition['defaultPlacement'])) {
            throw new InvalidArgumentException('FB Software AI widget placement must be an array.');
        }
        if (!in_array((string) $definition['refreshPolicy'], array('manual', 'screen_load', 'interval', 'event'), true)) {
            throw new InvalidArgumentException('Unsupported FB Software AI widget refresh policy.');
        }
        if (!is_array($definition['assetHandles']) || !is_array($definition['size']) || !is_array($definition['settingsSchema'])) {
            throw new InvalidArgumentException('Invalid FB Software AI widget array field.');
        }
    }
}
