<?php
/**
 * Contract for Workspace widget registries.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

interface WidgetRegistryInterface {
    /** @return void */
    public function register(WidgetDefinition $definition);

    /** @return WidgetDefinition|null */
    public function get($widget_id);

    /** @return WidgetDefinition[] */
    public function all();

    /** @return WidgetDefinition[] */
    public function for_context($context);

    /** @return WidgetDefinition[] */
    public function available_for_current_user($context);
}
