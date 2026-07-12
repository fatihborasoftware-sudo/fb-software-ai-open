<?php
/**
 * Central capability policy for the Workspace foundation.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class WorkspaceAccessPolicy {
    const MANAGE_CAPABILITY = 'manage_options';

    /** @return bool */
    public function can_access_current_user() {
        return function_exists('current_user_can') && current_user_can(self::MANAGE_CAPABILITY);
    }

    /** @return bool */
    public function can_manage_layout_current_user() {
        return $this->can_access_current_user();
    }

    /** @return bool */
    public function can_view_widget_current_user(WidgetDefinition $definition) {
        return function_exists('current_user_can') && current_user_can($definition->capability());
    }
}
