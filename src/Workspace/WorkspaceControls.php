<?php
/**
 * User-facing Workspace controls service.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use InvalidArgumentException;

final class WorkspaceControls {
    const CONTEXT = 'dashboard';

    /** @var WidgetRegistryInterface */
    private $registry;

    /** @var WorkspaceLayoutRepositoryInterface */
    private $layouts;

    /** @var DefaultWorkspaceLayoutRepository */
    private $defaults;

    /** @var WorkspaceAccessPolicy */
    private $access;

    /** @var DashboardPreferenceSynchronizer */
    private $synchronizer;

    public function __construct(
        WidgetRegistryInterface $registry,
        WorkspaceLayoutRepositoryInterface $layouts,
        DefaultWorkspaceLayoutRepository $defaults,
        WorkspaceAccessPolicy $access,
        DashboardPreferenceSynchronizer $synchronizer
    ) {
        $this->registry = $registry;
        $this->layouts = $layouts;
        $this->defaults = $defaults;
        $this->access = $access;
        $this->synchronizer = $synchronizer;
    }

    /**
     * Return the current user's normalized Dashboard layout and widget metadata.
     *
     * @param int $user_id WordPress user ID.
     * @return array<string,mixed>
     */
    public function state_for_user($user_id) {
        $user_id = (int) $user_id;
        $this->assert_user($user_id);
        $definitions = $this->available_definitions();
        $layout = $this->normalize_against_registry(
            $this->layouts->get_for_user($user_id, self::CONTEXT),
            $definitions
        );

        return array(
            'context' => self::CONTEXT,
            'layout' => $layout,
            'widgets' => $this->serialize_definitions($definitions, $layout),
            'screenOptionsAuthoritative' => true,
        );
    }

    /**
     * Save the authenticated user's layout after strict registry validation.
     *
     * @param int                 $user_id WordPress user ID.
     * @param array<string,mixed> $candidate Layout candidate.
     * @return array<string,mixed>
     */
    public function save_for_user($user_id, array $candidate) {
        $user_id = (int) $user_id;
        $this->assert_user($user_id);
        $definitions = $this->available_definitions();
        $definition_map = $this->definition_map($definitions);
        $candidate['context'] = self::CONTEXT;
        $candidate['layoutId'] = isset($candidate['layoutId']) ? $candidate['layoutId'] : 'fbsa-user-dashboard-v1';
        $candidate['source'] = 'user';
        $widgets = isset($candidate['widgets']) && is_array($candidate['widgets']) ? $candidate['widgets'] : array();

        $seen = array();
        $validated = array();
        foreach ($widgets as $position => $widget) {
            if (!is_array($widget)) {
                throw new InvalidArgumentException(__('Workspace widget records must be objects.', 'fb-software-ai'));
            }
            $id = isset($widget['id']) ? (string) $widget['id'] : '';
            if ($id === '' || !isset($definition_map[$id])) {
                throw new InvalidArgumentException(__('The Workspace request contains an unknown or unavailable widget.', 'fb-software-ai'));
            }
            if (isset($seen[$id])) {
                throw new InvalidArgumentException(__('The Workspace request contains a duplicate widget.', 'fb-software-ai'));
            }
            $seen[$id] = true;
            $default = $definition_map[$id]->default_placement();
            $requested_column = isset($widget['column']) ? (int) $widget['column'] : $this->column_number(isset($default['context']) ? $default['context'] : 'normal');
            $validated[] = array(
                'id' => $id,
                'visible' => $definition_map[$id]->removable()
                    ? (isset($widget['visible']) ? (bool) $widget['visible'] : true)
                    : true,
                'column' => max(1, min(4, $requested_column)),
                'order' => (int) $position,
                'collapsed' => isset($widget['collapsed']) ? (bool) $widget['collapsed'] : false,
                'width' => isset($widget['width']) ? $widget['width'] : null,
                'height' => isset($widget['height']) ? $widget['height'] : null,
                'settings' => isset($widget['settings']) && is_array($widget['settings']) ? $widget['settings'] : array(),
            );
        }

        foreach ($definitions as $definition) {
            if (isset($seen[$definition->id()])) {
                continue;
            }
            $placement = $definition->default_placement();
            $validated[] = array(
                'id' => $definition->id(),
                'visible' => true,
                'column' => $this->column_number(isset($placement['context']) ? $placement['context'] : 'normal'),
                'order' => count($validated),
                'collapsed' => false,
                'width' => null,
                'height' => null,
                'settings' => array(),
            );
        }

        $candidate['widgets'] = $validated;
        $layout = $this->layouts->save_for_user($user_id, $candidate);
        $layout = $this->normalize_against_registry($layout, $definitions);
        $this->synchronizer->synchronize($user_id, $layout, $definitions);

        return array(
            'context' => self::CONTEXT,
            'layout' => $layout,
            'widgets' => $this->serialize_definitions($definitions, $layout),
            'screenOptionsAuthoritative' => true,
        );
    }

    /**
     * Reset only the current user's FB Software AI Workspace preference.
     *
     * @param int $user_id WordPress user ID.
     * @return array<string,mixed>
     */
    public function reset_for_user($user_id) {
        $user_id = (int) $user_id;
        $this->assert_user($user_id);
        $this->layouts->reset_for_user($user_id, self::CONTEXT);
        $definitions = $this->available_definitions();
        $default = $this->normalize_against_registry($this->defaults->get(self::CONTEXT), $definitions);
        $this->synchronizer->reset($user_id, $default, $definitions);
        return $this->state_for_user($user_id);
    }

    /**
     * Filter the Dashboard registry result for the current user's saved layout.
     *
     * @param WidgetDefinition[]     $definitions Available widget definitions.
     * @param string                 $context Workspace context.
     * @param WidgetRegistryInterface $registry Registry instance.
     * @return WidgetDefinition[]
     */
    public function filter_current_user_widgets($definitions, $context, $registry) {
        if ((string) $context !== self::CONTEXT || !is_array($definitions) || !function_exists('get_current_user_id')) {
            return $definitions;
        }
        $user_id = (int) get_current_user_id();
        if ($user_id <= 0) {
            return $definitions;
        }
        $layout = $this->normalize_against_registry($this->layouts->get_for_user($user_id, self::CONTEXT), $definitions);
        $definition_map = $this->definition_map($definitions);
        $ordered = array();
        foreach ($layout['widgets'] as $widget) {
            if (!empty($widget['visible']) && isset($definition_map[$widget['id']])) {
                $ordered[] = $definition_map[$widget['id']];
            }
        }
        return $ordered;
    }

    /** @return WidgetDefinition[] */
    private function available_definitions() {
        $definitions = $this->registry->for_context(self::CONTEXT);
        $available = array();
        foreach ($definitions as $definition) {
            if ($definition instanceof WidgetDefinition && $this->access->can_view_widget_current_user($definition)) {
                $available[] = $definition;
            }
        }
        return $available;
    }

    /**
     * @param array<string,mixed>|null $layout Layout candidate.
     * @param WidgetDefinition[]       $definitions Definitions.
     * @return array<string,mixed>
     */
    private function normalize_against_registry($layout, array $definitions) {
        $layout = is_array($layout) ? $layout : array(
            'context' => self::CONTEXT,
            'layoutId' => 'fbsa-default-dashboard-v1',
            'source' => 'default',
            'widgets' => array(),
        );
        $definition_map = $this->definition_map($definitions);
        $normalized = array();
        $seen = array();
        $widgets = isset($layout['widgets']) && is_array($layout['widgets']) ? $layout['widgets'] : array();
        foreach ($widgets as $widget) {
            if (!is_array($widget) || empty($widget['id']) || !isset($definition_map[$widget['id']]) || isset($seen[$widget['id']])) {
                continue;
            }
            $seen[$widget['id']] = true;
            $normalized[] = $widget;
        }
        foreach ($definitions as $definition) {
            if (isset($seen[$definition->id()])) {
                continue;
            }
            $placement = $definition->default_placement();
            $normalized[] = array(
                'id' => $definition->id(),
                'visible' => true,
                'column' => $this->column_number(isset($placement['context']) ? $placement['context'] : 'normal'),
                'order' => count($normalized),
                'collapsed' => false,
                'width' => null,
                'height' => null,
                'settings' => array(),
            );
        }
        foreach ($normalized as $index => &$widget) {
            $widget['order'] = $index;
        }
        unset($widget);
        $layout['context'] = self::CONTEXT;
        $layout['widgets'] = $normalized;
        return $layout;
    }

    /** @return array<string,WidgetDefinition> */
    private function definition_map(array $definitions) {
        $map = array();
        foreach ($definitions as $definition) {
            if ($definition instanceof WidgetDefinition) {
                $map[$definition->id()] = $definition;
            }
        }
        return $map;
    }

    /** @return array<int,array<string,mixed>> */
    private function serialize_definitions(array $definitions, array $layout) {
        $layout_map = array();
        foreach ($layout['widgets'] as $widget) {
            if (is_array($widget) && !empty($widget['id'])) {
                $layout_map[$widget['id']] = $widget;
            }
        }
        $rows = array();
        foreach ($definitions as $definition) {
            $widget = isset($layout_map[$definition->id()]) ? $layout_map[$definition->id()] : array();
            $rows[] = array(
                'id' => $definition->id(),
                'title' => $definition->title(),
                'description' => $definition->description(),
                'removable' => $definition->removable(),
                'visible' => !isset($widget['visible']) || (bool) $widget['visible'],
                'column' => isset($widget['column']) ? (int) $widget['column'] : 1,
                'order' => isset($widget['order']) ? (int) $widget['order'] : count($rows),
            );
        }
        usort($rows, function (array $left, array $right) {
            return $left['order'] <=> $right['order'];
        });
        return $rows;
    }

    /** @return int */
    private function column_number($context) {
        $map = array('normal' => 1, 'side' => 2, 'column3' => 3, 'column4' => 4);
        return isset($map[(string) $context]) ? $map[(string) $context] : 1;
    }

    /** @return void */
    private function assert_user($user_id) {
        if (!$this->access->can_manage_user($user_id)) {
            throw new InvalidArgumentException(__('You are not allowed to manage this Workspace layout.', 'fb-software-ai'));
        }
    }
}
