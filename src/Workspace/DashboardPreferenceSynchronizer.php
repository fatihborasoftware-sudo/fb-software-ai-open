<?php
/**
 * Safely synchronizes only FB Software AI widget ordering with WordPress.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class DashboardPreferenceSynchronizer {
    const ORDER_OPTION = 'meta-box-order_dashboard';

    /**
     * Merge the FB Software AI layout into the native Dashboard order while
     * preserving all WordPress core and third-party widget IDs.
     *
     * @param int                 $user_id WordPress user ID.
     * @param array<string,mixed> $layout Normalized Workspace layout.
     * @param WidgetDefinition[]  $definitions Registered Dashboard widgets.
     * @return array<string,string>
     */
    public function synchronize($user_id, array $layout, array $definitions) {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return array();
        }

        $definition_map = array();
        foreach ($definitions as $definition) {
            if (!$definition instanceof WidgetDefinition) {
                continue;
            }
            $definition_map[$definition->id()] = $definition;
        }

        $current = get_user_option(self::ORDER_OPTION, $user_id);
        if (!is_array($current)) {
            // Do not create a native Dashboard order option for users who still
            // rely on WordPress defaults. Registry ordering remains effective.
            return array();
        }
        $columns = array('normal', 'side', 'column3', 'column4');
        $merged = array();

        foreach ($columns as $column) {
            $ids = $this->parse_ids(isset($current[$column]) ? $current[$column] : '');
            $merged[$column] = array_values(array_filter($ids, function ($id) use ($definition_map) {
                return !isset($definition_map[$id]);
            }));
        }

        foreach ($layout['widgets'] as $widget) {
            if (!is_array($widget) || empty($widget['id']) || !isset($definition_map[$widget['id']])) {
                continue;
            }
            $definition = $definition_map[$widget['id']];
            $column = $this->column_from_layout($widget, $definition);
            $merged[$column][] = $definition->id();
        }

        $serialized = array();
        foreach ($columns as $column) {
            $serialized[$column] = implode(',', array_values(array_unique($merged[$column])));
        }
        update_user_option($user_id, self::ORDER_OPTION, $serialized, false);

        if (function_exists('do_action')) {
            do_action('fbsa_workspace_dashboard_order_synchronized', $serialized, $user_id, $layout);
        }

        return $serialized;
    }

    /**
     * Remove FB Software AI IDs from native ordering during a reset, then append
     * the default layout in its defined columns.
     *
     * @param int                 $user_id WordPress user ID.
     * @param array<string,mixed> $default_layout Default layout.
     * @param WidgetDefinition[]  $definitions Registered Dashboard widgets.
     * @return array<string,string>
     */
    public function reset($user_id, array $default_layout, array $definitions) {
        return $this->synchronize($user_id, $default_layout, $definitions);
    }

    /** @return string[] */
    private function parse_ids($value) {
        $ids = is_array($value) ? $value : explode(',', (string) $value);
        $clean = array();
        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id !== '' && preg_match('/^[a-z][a-z0-9_.-]{2,190}$/', $id)) {
                $clean[] = $id;
            }
        }
        return array_values(array_unique($clean));
    }

    /** @return string */
    private function column_from_layout(array $widget, WidgetDefinition $definition) {
        $placement = $definition->default_placement();
        $default = isset($placement['context']) ? (string) $placement['context'] : 'normal';
        $column = isset($widget['column']) ? (int) $widget['column'] : 0;
        $map = array(1 => 'normal', 2 => 'side', 3 => 'column3', 4 => 'column4');
        if (isset($map[$column])) {
            return $map[$column];
        }
        return in_array($default, array('normal', 'side', 'column3', 'column4'), true) ? $default : 'normal';
    }
}
