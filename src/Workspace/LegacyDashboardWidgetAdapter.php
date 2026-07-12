<?php
/**
 * Registers the four legacy Dashboard widgets in the new Widget Registry.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class LegacyDashboardWidgetAdapter {
    /** @var WidgetRegistryInterface */
    private $registry;

    /** @var bool */
    private $attached = false;

    public function __construct(WidgetRegistryInterface $registry) {
        $this->registry = $registry;
    }

    /**
     * Register the preserved widgets and connect the legacy renderer facade.
     *
     * @param object $legacy_facade Existing plugin facade.
     * @return void
     */
    public function attach($legacy_facade) {
        if ($this->attached) {
            return;
        }
        $this->register_core_widgets();
        if (is_object($legacy_facade) && method_exists($legacy_facade, 'set_workspace_widget_registry')) {
            $legacy_facade->set_workspace_widget_registry($this->registry);
        }
        $this->attached = true;
    }

    /** @return void */
    private function register_core_widgets() {
        $widgets = array(
            array(
                'id' => 'fbsa_website_steps_widget',
                'title' => 'FB Software AI',
                'description' => 'Website setup checklist and guide links.',
                'renderer' => 'render_dashboard_website_steps_widget',
                'defaultPlacement' => array('context' => 'column3', 'priority' => 'high', 'order' => 0),
            ),
            array(
                'id' => 'fbsa_plugin_setup_widget',
                'title' => 'FB Software AI — Plugin Setup',
                'description' => 'Required plugin setup checklist and guide links.',
                'renderer' => 'render_dashboard_plugin_setup_widget',
                'defaultPlacement' => array('context' => 'column4', 'priority' => 'high', 'order' => 2),
            ),
            array(
                'id' => 'fbsa_website_settings_widget',
                'title' => 'FB Software AI — Website Settings',
                'description' => 'Website settings checklist and guide links.',
                'renderer' => 'render_dashboard_website_settings_widget',
                'defaultPlacement' => array('context' => 'column3', 'priority' => 'default', 'order' => 1),
            ),
            array(
                'id' => 'fbsa_help_tutorials_widget',
                'title' => 'FB Software AI — Help and Tutorials',
                'description' => 'Help, tutorials, and learning resources.',
                'renderer' => 'render_dashboard_help_tutorials_widget',
                'defaultPlacement' => array('context' => 'column4', 'priority' => 'default', 'order' => 3),
            ),
        );

        foreach ($widgets as $widget) {
            if ($this->registry->get($widget['id'])) {
                continue;
            }
            $this->registry->register(new WidgetDefinition(array_merge(array(
                'schemaVersion' => 1,
                'version' => '1.0.0',
                'capability' => WorkspaceAccessPolicy::MANAGE_CAPABILITY,
                'contexts' => array('dashboard'),
                'assetHandles' => array('fbsa-dashboard-welcome'),
                'removable' => true,
                'collapsible' => true,
                'refreshPolicy' => 'screen_load',
                'settingsSchema' => array(),
            ), $widget)));
        }
    }
}
