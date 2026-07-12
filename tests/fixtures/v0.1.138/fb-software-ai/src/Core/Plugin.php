<?php
/**
 * Architecture foundation bootstrap.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

use FBSoftwareAI\Migrations\Core\Version0001Baseline;
use FBSoftwareAI\Migrations\Workspace\Version0002WorkspaceFoundation;
use FBSoftwareAI\Migrations\MigrationResult;
use FBSoftwareAI\Migrations\MigrationRunner;
use FBSoftwareAI\Migrations\SchemaVersionRepository;
use FBSoftwareAI\Settings\LegacySettingsRepository;
use FBSoftwareAI\Workspace\WorkspaceModule;

final class Plugin {
    /** @var string */
    private $plugin_file;

    /** @var Container */
    private $container;

    /** @var ModuleRegistry */
    private $modules;

    /** @var bool */
    private $registered = false;

    public function __construct($plugin_file) {
        $this->plugin_file = (string) $plugin_file;
        $this->container = new Container();
        $this->modules = new ModuleRegistry();
    }

    /**
     * Register foundation services, modules, activation, and admin upgrade path.
     *
     * @return void
     */
    public function register() {
        if ($this->registered) {
            return;
        }

        $this->container->set('plugin.file', $this->plugin_file);
        $this->container->set('container', $this->container);
        $this->container->set('module.registry', $this->modules);
        $this->container->factory('settings.legacy', function () {
            return new LegacySettingsRepository();
        });
        $this->container->factory('schemas', function () {
            return new SchemaVersionRepository();
        });
        $this->container->factory('migrations', function (Container $container) {
            return new MigrationRunner(
                $container->get('schemas'),
                $container->get('settings.legacy'),
                Version::plugin()
            );
        });

        $this->modules->add(new FoundationModule());
        $this->modules->add(new WorkspaceModule());
        $this->modules->register_all($this->container);

        register_activation_hook($this->plugin_file, array($this, 'activate'));
        add_action('admin_init', array($this, 'maybe_upgrade'), 5);
        $this->registered = true;
    }

    /**
     * Activation entry point. Migrations 0001 and 0002 are additive and idempotent.
     *
     * @param bool $network_wide Whether activation was network-wide.
     * @return void
     */
    public function activate($network_wide = false) {
        if ($network_wide && function_exists('is_multisite') && is_multisite() && function_exists('get_sites')) {
            $site_ids = get_sites(array('fields' => 'ids', 'number' => 0));
            foreach ($site_ids as $site_id) {
                switch_to_blog((int) $site_id);
                $this->run_migrations();
                restore_current_blog();
            }
            return;
        }

        $this->run_migrations();
    }

    /**
     * Run the migration only in an authorized admin request when needed.
     *
     * @return void
     */
    public function maybe_upgrade() {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        $schemas = $this->container->get('schemas');
        if (
            $schemas->version('core') >= Version::core_schema()
            && $schemas->version('workspace') >= Version::workspace_schema()
        ) {
            return;
        }

        $this->run_migrations();
    }

    /**
     * Attach the legacy compatibility facade.
     *
     * @param object $legacy_facade Existing facade.
     * @return void
     */
    public function attach_legacy_facade($legacy_facade) {
        if (!is_object($legacy_facade)) {
            return;
        }

        $this->container->set('legacy.facade', $legacy_facade);
        if ($this->container->has('workspace.dashboard.adapter')) {
            $this->container->get('workspace.dashboard.adapter')->attach($legacy_facade);
        }
    }

    /** @return Container */
    public function container() {
        return $this->container;
    }

    /**
     * @return MigrationResult
     */
    private function run_migrations() {
        $runner = $this->container->get('migrations');
        $result = $runner->run(array(
            new Version0001Baseline(),
            new Version0002WorkspaceFoundation(),
        ));

        if ($result->status() === MigrationResult::STATUS_FAILED && function_exists('error_log')) {
            error_log(
                sprintf(
                    'FB Software AI migration failed (%s): %s',
                    $result->request_id(),
                    $result->message()
                )
            );
        }

        return $result;
    }
}
