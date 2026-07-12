<?php
/**
 * Workspace, Widget Registry, and user controls module.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use FBSoftwareAI\Core\Container;
use FBSoftwareAI\Core\ModuleInterface;
use FBSoftwareAI\Core\Version;

final class WorkspaceModule implements ModuleInterface {
    /** @var Container|null */
    private $container;

    /** @var bool */
    private $extensions_registered = false;

    /** @return string */
    public function id() {
        return 'workspace';
    }

    /** @return void */
    public function register(Container $container) {
        $this->container = $container;
        $container->set('schema.workspace.target', Version::workspace_schema());
        $container->factory('workspace.widget.registry', function () {
            return new WidgetRegistry();
        });
        $container->factory('workspace.access', function () {
            return new WorkspaceAccessPolicy();
        });
        $container->factory('workspace.defaults', function () {
            return new DefaultWorkspaceLayoutRepository();
        });
        $container->factory('workspace.layout.validator', function () {
            return new WorkspaceLayoutValidator();
        });
        $container->factory('workspace.layouts', function (Container $services) {
            return new UserWorkspaceLayoutRepository(
                $services->get('workspace.defaults'),
                $services->get('workspace.layout.validator')
            );
        });
        $container->factory('workspace.dashboard.preferences', function () {
            return new DashboardPreferenceSynchronizer();
        });
        $container->factory('workspace.controls', function (Container $services) {
            return new WorkspaceControls(
                $services->get('workspace.widget.registry'),
                $services->get('workspace.layouts'),
                $services->get('workspace.defaults'),
                $services->get('workspace.access'),
                $services->get('workspace.dashboard.preferences')
            );
        });
        $container->factory('workspace.rest', function (Container $services) {
            return new WorkspaceRestController(
                $services->get('workspace.controls'),
                $services->get('workspace.access')
            );
        });
        $container->factory('workspace.settings', function (Container $services) {
            return new WorkspaceSettingsRenderer(
                $services->get('workspace.controls'),
                $services->get('workspace.access'),
                $services->get('plugin.file')
            );
        });
        $container->factory('workspace.dashboard.adapter', function (Container $services) {
            return new LegacyDashboardWidgetAdapter($services->get('workspace.widget.registry'));
        });

        add_filter(
            'fbsa_workspace_widgets_for_current_user',
            array($container->get('workspace.controls'), 'filter_current_user_widgets'),
            10,
            3
        );
        add_action('rest_api_init', array($container->get('workspace.rest'), 'register_routes'));
        $container->get('workspace.settings')->register();
        add_action('plugins_loaded', array($this, 'register_extensions'), 20);
    }

    /**
     * Allow local third-party modules to register validated widgets.
     *
     * @return void
     */
    public function register_extensions() {
        if ($this->extensions_registered || !$this->container) {
            return;
        }
        $this->extensions_registered = true;
        do_action(
            'fbsa_register_widgets',
            $this->container->get('workspace.widget.registry'),
            $this->container
        );
    }
}
