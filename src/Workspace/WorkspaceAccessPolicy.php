<?php
/**
 * Central capability policy for the Workspace controls.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class WorkspaceAccessPolicy {
    const MANAGE_CAPABILITY = 'manage_options';

    /**
     * Return the capability required to manage the current user's Workspace.
     *
     * @param int $user_id WordPress user ID.
     * @return string
     */
    public function manage_capability($user_id = 0) {
        $capability = self::MANAGE_CAPABILITY;
        if (function_exists('apply_filters')) {
            $filtered = apply_filters('fbsa_workspace_manage_capability', $capability, (int) $user_id);
            if (is_string($filtered) && trim($filtered) !== '') {
                $capability = trim($filtered);
            }
        }
        return $capability;
    }

    /** @return bool */
    public function can_access_current_user() {
        return $this->can_manage_layout_current_user();
    }

    /** @return bool */
    public function can_manage_layout_current_user() {
        return function_exists('current_user_can')
            && current_user_can($this->manage_capability($this->current_user_id()));
    }

    /**
     * @param int $user_id WordPress user ID.
     * @return bool
     */
    public function can_manage_user($user_id) {
        $user_id = (int) $user_id;
        if ($user_id <= 0 || !function_exists('get_current_user_id')) {
            return false;
        }
        if ((int) get_current_user_id() !== $user_id) {
            return false;
        }
        return $this->can_manage_layout_current_user();
    }

    /** @return bool */
    public function can_view_widget_current_user(WidgetDefinition $definition) {
        return function_exists('current_user_can') && current_user_can($definition->capability());
    }

    /** @return int */
    private function current_user_id() {
        return function_exists('get_current_user_id') ? (int) get_current_user_id() : 0;
    }
}
