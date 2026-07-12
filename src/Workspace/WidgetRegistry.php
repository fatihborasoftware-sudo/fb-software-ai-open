<?php
/**
 * Capability-aware registry for Workspace widgets.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use RuntimeException;

final class WidgetRegistry implements WidgetRegistryInterface {
    /** @var array<string,WidgetDefinition> */
    private $definitions = array();

    /**
     * @param WidgetDefinition $definition Widget definition.
     * @return void
     */
    public function register(WidgetDefinition $definition) {
        $id = $definition->id();
        if (isset($this->definitions[$id])) {
            throw new RuntimeException('Duplicate FB Software AI widget: ' . $id);
        }

        if (function_exists('apply_filters')) {
            $presentation = array(
                'title'            => $definition->title(),
                'description'      => $definition->description(),
                'defaultPlacement' => $definition->default_placement(),
                'size'             => $definition->size(),
                'removable'        => $definition->removable(),
                'collapsible'      => $definition->collapsible(),
            );
            $filtered = apply_filters('fbsa_widget_definition', $presentation, $id, $definition);
            if (is_array($filtered)) {
                try {
                    $definition = $definition->with_presentation($filtered);
                } catch (\InvalidArgumentException $error) {
                    // Invalid presentation filters are ignored; the secure original remains authoritative.
                }
            }
        }

        $this->definitions[$id] = $definition;

        if (function_exists('do_action')) {
            do_action('fbsa_widget_registered', $definition, $this);
        }
    }

    /** @return WidgetDefinition|null */
    public function get($widget_id) {
        $widget_id = (string) $widget_id;
        return isset($this->definitions[$widget_id]) ? $this->definitions[$widget_id] : null;
    }

    /** @return WidgetDefinition[] */
    public function all() {
        return array_values($this->definitions);
    }

    /** @return WidgetDefinition[] */
    public function for_context($context) {
        $context = (string) $context;
        $matches = array();
        foreach ($this->definitions as $definition) {
            if (in_array($context, $definition->contexts(), true)) {
                $matches[] = $definition;
            }
        }

        usort($matches, function (WidgetDefinition $left, WidgetDefinition $right) {
            $left_placement = $left->default_placement();
            $right_placement = $right->default_placement();
            $left_key = sprintf('%04d:%s', isset($left_placement['order']) ? (int) $left_placement['order'] : 0, $left->id());
            $right_key = sprintf('%04d:%s', isset($right_placement['order']) ? (int) $right_placement['order'] : 0, $right->id());
            return strcmp($left_key, $right_key);
        });

        return $matches;
    }

    /** @return WidgetDefinition[] */
    public function available_for_current_user($context) {
        $available = array();
        foreach ($this->for_context($context) as $definition) {
            if (!function_exists('current_user_can') || current_user_can($definition->capability())) {
                $available[] = $definition;
            }
        }

        if (function_exists('apply_filters')) {
            $filtered = apply_filters('fbsa_workspace_widgets_for_current_user', $available, $context, $this);
            if (is_array($filtered)) {
                $available = array_values(array_filter($filtered, function ($definition) {
                    return $definition instanceof WidgetDefinition;
                }));
            }
        }

        return $available;
    }
}
