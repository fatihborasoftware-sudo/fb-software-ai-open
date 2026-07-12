<?php
/**
 * Workspace and Widget Registry foundation module.
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
        $container->factory('workspace.dashboard.adapter', function (Container $services) {
            return new LegacyDashboardWidgetAdapter($services->get('workspace.widget.registry'));
        });

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
