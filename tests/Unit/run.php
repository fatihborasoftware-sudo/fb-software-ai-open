<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$root = dirname(__DIR__, 2);
require_once $root . '/src/Core/Autoloader.php';
\FBSoftwareAI\Core\Autoloader::register($root . '/src');

use FBSoftwareAI\Core\Container;
use FBSoftwareAI\Core\ModuleInterface;
use FBSoftwareAI\Core\ModuleRegistry;
use FBSoftwareAI\Migrations\Core\Version0001Baseline;
use FBSoftwareAI\Migrations\Workspace\Version0002WorkspaceFoundation;
use FBSoftwareAI\Migrations\Workspace\Version0003WorkspaceControls;
use FBSoftwareAI\Migrations\MigrationContext;
use FBSoftwareAI\Migrations\MigrationInterface;
use FBSoftwareAI\Migrations\MigrationRunner;
use FBSoftwareAI\Migrations\MigrationSnapshot;
use FBSoftwareAI\Migrations\SchemaVersionRepository;
use FBSoftwareAI\Settings\LegacySettingsRepository;
use FBSoftwareAI\Workspace\DashboardPreferenceSynchronizer;
use FBSoftwareAI\Workspace\DefaultLayoutFactory;
use FBSoftwareAI\Workspace\DefaultWorkspaceLayoutRepository;
use FBSoftwareAI\Workspace\LegacyDashboardWidgetAdapter;
use FBSoftwareAI\Workspace\UserWorkspaceLayoutRepository;
use FBSoftwareAI\Workspace\WidgetDefinition;
use FBSoftwareAI\Workspace\WidgetRegistry;
use FBSoftwareAI\Workspace\WorkspaceAccessPolicy;
use FBSoftwareAI\Workspace\WorkspaceControls;
use FBSoftwareAI\Workspace\WorkspaceLayoutValidator;
use FBSoftwareAI\Workspace\WorkspaceRestController;

// Container tests.
$container = new Container();
$container->set('value', 42);
fbsa_assert_same(42, $container->get('value'), 'Container returns concrete values.');
$factory_calls = 0;
$container->factory('shared', function (Container $c) use (&$factory_calls) {
    $factory_calls++;
    return (object) array('value' => $c->get('value'));
});
$first = $container->get('shared');
$second = $container->get('shared');
fbsa_assert_true($first === $second, 'Container factories resolve as shared services.');
fbsa_assert_same(1, $factory_calls, 'Container factory runs once.');
$container->factory('circular', function (Container $c) { return $c->get('circular'); });
$circular_failed = false;
try { $container->get('circular'); } catch (RuntimeException $error) { $circular_failed = true; }
fbsa_assert_true($circular_failed, 'Container rejects circular service resolution.');

// Module registry tests.
$module_calls = 0;
$module = new class($module_calls) implements ModuleInterface {
    private $calls;
    public function __construct(&$calls) { $this->calls =& $calls; }
    public function id() { return 'test'; }
    public function register(Container $container) { $this->calls++; $container->set('module.test', true); }
};
$registry = new ModuleRegistry();
$registry->add($module);
$registry->register_all($container);
$registry->register_all($container);
fbsa_assert_same(1, $module_calls, 'Module registry registers each module once.');
fbsa_assert_true($container->get('module.test') === true, 'Module can register services.');
$duplicate_failed = false;
try { $registry->add($module); } catch (RuntimeException $error) { $duplicate_failed = true; }
fbsa_assert_true($duplicate_failed, 'Module registry rejects duplicate identifiers.');

// Legacy settings merge tests.
$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array(
        'known' => 'before',
        'unknown' => 'preserve-me',
        'nested' => array('keep' => 'yes', 'change' => 'before'),
        'list' => array('a', 'b'),
    ),
);
$settings = new LegacySettingsRepository();
$updated = $settings->merge(array(
    'known' => 'after',
    'nested' => array('change' => 'after'),
    'list' => array('replacement'),
));
fbsa_assert_same('preserve-me', $updated['unknown'], 'Settings merge preserves unknown top-level fields.');
fbsa_assert_same('yes', $updated['nested']['keep'], 'Settings merge preserves unknown nested fields.');
fbsa_assert_same('after', $updated['nested']['change'], 'Settings merge updates known nested fields.');
fbsa_assert_same(array('replacement'), $updated['list'], 'Settings merge replaces list values safely.');

// Migrations 0001 through 0003 apply without changing legacy settings.
$settings_before = $GLOBALS['fbsa_test_options']['fbsa_demo_settings'];
$schemas = new SchemaVersionRepository();
$runner = new MigrationRunner($schemas, $settings, '0.1.139');
$result = $runner->run(array(new Version0001Baseline(), new Version0002WorkspaceFoundation(), new Version0003WorkspaceControls()), 'request-0001');
fbsa_assert_same('applied', $result->status(), 'Architecture and Workspace migrations report applied.');
fbsa_assert_same(1, $schemas->version('core'), 'Migration 0001 advances the core schema.');
fbsa_assert_same(2, $schemas->version('workspace'), 'Migration 0003 advances the Workspace schema.');
$schema_map = $schemas->all();
fbsa_assert_same('0.1.139', $schema_map['_meta']['plugin_baseline'], 'Migrations record the current plugin baseline.');
fbsa_assert_same('request-0001', $schema_map['_meta']['migration_request_id'], 'Migrations record request ID.');
fbsa_assert_same('0003-workspace-controls', $schema_map['_meta']['last_migration_id'], 'Latest migration ID is recorded.');
fbsa_assert_same($settings_before, $GLOBALS['fbsa_test_options']['fbsa_demo_settings'], 'Migrations leave legacy settings unchanged.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY]), 'Migration 0002 seeds the default Workspace layout option.');
fbsa_assert_same('dashboard', $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY]['layouts']['dashboard']['context'], 'Default Workspace option contains the Dashboard layout.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY]['controlsVersion'], 'Migration 0003 marks the Workspace defaults as controls-ready.');
fbsa_assert_true(!array_key_exists('fbsa_migration_lock', $GLOBALS['fbsa_test_options']), 'Migration lock is released.');

// Idempotency.
$map_before_second_run = $schemas->all();
$defaults_before_second_run = $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY];
$second = $runner->run(array(new Version0001Baseline(), new Version0002WorkspaceFoundation(), new Version0003WorkspaceControls()), 'request-0002');
fbsa_assert_same('noop', $second->status(), 'Migrations are idempotent.');
fbsa_assert_same($map_before_second_run, $schemas->all(), 'Idempotent run does not rewrite schema metadata.');
fbsa_assert_same($defaults_before_second_run, $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY], 'Idempotent run does not rewrite default layouts.');

// Existing Workspace defaults and unknown metadata are preserved.
$custom_defaults = array('schemaVersion' => 1, 'layouts' => array('dashboard' => array('custom' => 'keep')));
$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array('unknown' => 'same'),
    'fbsa_schema_versions' => array('workspace' => 1, '_meta' => array('existing' => 'keep')),
    DefaultLayoutFactory::OPTION_KEY => $custom_defaults,
);
$settings = new LegacySettingsRepository();
$schemas = new SchemaVersionRepository();
$runner = new MigrationRunner($schemas, $settings, '0.1.139');
$preserve = $runner->run(array(new Version0001Baseline(), new Version0002WorkspaceFoundation(), new Version0003WorkspaceControls()), 'request-0003');
$map = $schemas->all();
fbsa_assert_same('applied', $preserve->status(), 'Core baseline applies to a partial schema map.');
fbsa_assert_same(2, $map['workspace'], 'Existing Workspace schema advances to the controls version.');
fbsa_assert_same('keep', $map['_meta']['existing'], 'Unknown schema metadata is preserved.');
fbsa_assert_same($custom_defaults['layouts'], $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY]['layouts'], 'Existing Workspace layouts are not overwritten.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options'][DefaultLayoutFactory::OPTION_KEY]['controlsVersion'], 'Existing Workspace defaults receive only the controls marker.');

// Active lock blocks a second runner.
$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array(),
    'fbsa_migration_lock' => array('owner' => 'other', 'expires_at' => time() + 60),
);
$settings = new LegacySettingsRepository();
$schemas = new SchemaVersionRepository();
$runner = new MigrationRunner($schemas, $settings, '0.1.139');
$locked = $runner->run(array(new Version0001Baseline(), new Version0002WorkspaceFoundation(), new Version0003WorkspaceControls()), 'request-locked');
fbsa_assert_same('locked', $locked->status(), 'Concurrent migration request is blocked.');
fbsa_assert_same(0, $schemas->version('core'), 'Locked migration does not advance core schema.');
fbsa_assert_same(0, $schemas->version('workspace'), 'Locked migration does not advance Workspace schema.');

// Failure restores schema and Workspace defaults.
$GLOBALS['fbsa_test_options'] = array('fbsa_demo_settings' => array('preserve' => 'yes'));
$settings = new LegacySettingsRepository();
$schemas = new SchemaVersionRepository();
$runner = new MigrationRunner($schemas, $settings, '0.1.139');
$failing = new class implements MigrationInterface {
    public function id() { return '9999-failing'; }
    public function module() { return 'workspace'; }
    public function from_version() { return 0; }
    public function to_version() { return 1; }
    public function preflight(MigrationContext $context) { return true; }
    public function up(MigrationContext $context) { throw new RuntimeException('deliberate test failure'); }
    public function verify(MigrationContext $context) { return false; }
    public function recover(MigrationContext $context, MigrationSnapshot $snapshot) { $context->schemas()->restore($snapshot->schema_snapshot()); }
};
$failed = $runner->run(array($failing), 'request-failed');
fbsa_assert_same('failed', $failed->status(), 'Failed migration reports failure.');
fbsa_assert_true(!array_key_exists('fbsa_schema_versions', $GLOBALS['fbsa_test_options']), 'Failed migration restores missing schema option.');
fbsa_assert_true(!array_key_exists('fbsa_migration_lock', $GLOBALS['fbsa_test_options']), 'Failed migration releases its lock.');
fbsa_assert_same(array('preserve' => 'yes'), $GLOBALS['fbsa_test_options']['fbsa_demo_settings'], 'Failed migration preserves legacy settings.');

// Widget definition and registry.
$GLOBALS['fbsa_test_filters'] = array();
$GLOBALS['fbsa_test_actions'] = array();
$widget_registry = new WidgetRegistry();
$widget = new WidgetDefinition(array(
    'id' => 'fbsa_test_widget',
    'version' => '1.0.0',
    'title' => 'Test Widget',
    'capability' => 'manage_options',
    'renderer' => 'render_test_widget',
    'contexts' => array('dashboard', 'workspace'),
    'defaultPlacement' => array('context' => 'column3', 'priority' => 'high', 'order' => 5),
));
$widget_registry->register($widget);
fbsa_assert_true($widget_registry->get('fbsa_test_widget') instanceof WidgetDefinition, 'Widget Registry returns registered definitions.');
fbsa_assert_same(1, count($widget_registry->for_context('workspace')), 'Widget Registry filters by context.');
fbsa_assert_same(1, count($widget_registry->available_for_current_user('dashboard')), 'Widget Registry applies current-user capability checks.');
$GLOBALS['fbsa_test_capabilities']['manage_options'] = false;
fbsa_assert_same(0, count($widget_registry->available_for_current_user('dashboard')), 'Unavailable widgets are excluded when capability is missing.');
$GLOBALS['fbsa_test_capabilities']['manage_options'] = true;
$duplicate_widget_failed = false;
try { $widget_registry->register($widget); } catch (RuntimeException $error) { $duplicate_widget_failed = true; }
fbsa_assert_true($duplicate_widget_failed, 'Widget Registry rejects duplicate widget IDs.');
$invalid_widget_failed = false;
try { new WidgetDefinition(array('id' => 'Bad ID', 'title' => 'Bad', 'renderer' => 'x')); } catch (InvalidArgumentException $error) { $invalid_widget_failed = true; }
fbsa_assert_true($invalid_widget_failed, 'Widget definitions reject invalid IDs.');

// Legacy adapter registers the preserved four widgets.
$widget_registry = new WidgetRegistry();
$legacy = new class {
    public $registry;
    public function set_workspace_widget_registry($registry) { $this->registry = $registry; }
};
$adapter = new LegacyDashboardWidgetAdapter($widget_registry);
$adapter->attach($legacy);
$ids = array_map(function (WidgetDefinition $definition) { return $definition->id(); }, $widget_registry->for_context('dashboard'));
sort($ids);
fbsa_assert_same(array(
    'fbsa_help_tutorials_widget',
    'fbsa_plugin_setup_widget',
    'fbsa_website_settings_widget',
    'fbsa_website_steps_widget',
), $ids, 'Legacy adapter registers all four preserved Dashboard widget IDs.');
fbsa_assert_true($legacy->registry === $widget_registry, 'Legacy facade receives the modular Widget Registry.');

// Per-user Workspace layout repository.
$GLOBALS['fbsa_test_options'] = array(DefaultLayoutFactory::OPTION_KEY => DefaultLayoutFactory::option_value());
$GLOBALS['fbsa_test_user_options'] = array();
$defaults = new DefaultWorkspaceLayoutRepository();
$validator = new WorkspaceLayoutValidator();
$layouts = new UserWorkspaceLayoutRepository($defaults, $validator);
$default_layout = $layouts->get_for_user(7, 'dashboard');
fbsa_assert_same('default', $default_layout['source'], 'Layout repository falls back to the site default.');
fbsa_assert_same(4, count($default_layout['widgets']), 'Default Dashboard layout contains four widgets.');
$saved = $layouts->save_for_user(7, array(
    'context' => 'dashboard',
    'layoutId' => '<b>custom-layout</b>',
    'widgets' => array(
        array('id' => 'fbsa_website_steps_widget', 'visible' => false, 'column' => 99, 'order' => -5, 'settings' => array('Label!' => '<script>alert(1)</script> Safe')),
        array('id' => 'fbsa_website_steps_widget', 'visible' => true, 'column' => 1, 'order' => 1),
        array('id' => 'invalid id', 'visible' => true, 'column' => 1, 'order' => 2),
    ),
));
fbsa_assert_same('custom-layout', $saved['layoutId'], 'Layout IDs are sanitized.');
fbsa_assert_same(1, count($saved['widgets']), 'Duplicate and invalid widget records are removed.');
fbsa_assert_same(12, $saved['widgets'][0]['column'], 'Widget columns are clamped to the supported grid.');
fbsa_assert_same(0, $saved['widgets'][0]['order'], 'Widget order is clamped to zero.');
fbsa_assert_same('alert(1) Safe', $saved['widgets'][0]['settings']['label'], 'Widget settings are sanitized recursively.');
$loaded = $layouts->get_for_user(7, 'dashboard');
fbsa_assert_same('user', $loaded['source'], 'Saved user layout overrides the default.');
fbsa_assert_true($layouts->reset_for_user(7, 'dashboard'), 'User layout reset succeeds.');
$reset = $layouts->get_for_user(7, 'dashboard');
fbsa_assert_same('default', $reset['source'], 'Reset restores the default layout fallback.');


// Workspace Controls: strict registry validation, per-user isolation, visibility, order, and reset.
$GLOBALS['fbsa_test_current_user'] = 7;
$GLOBALS['fbsa_test_capabilities'] = array('manage_options' => true);
$GLOBALS['fbsa_test_options'] = array(DefaultLayoutFactory::OPTION_KEY => array_merge(DefaultLayoutFactory::option_value(), array('controlsVersion' => 1)));
$GLOBALS['fbsa_test_user_options'] = array(
    7 => array(
        'meta-box-order_dashboard' => array(
            'normal' => 'dashboard_activity',
            'side' => 'wpvivid_dashboard_widget',
            'column3' => 'fbsa_website_steps_widget,fbsa_website_settings_widget',
            'column4' => 'fbsa_plugin_setup_widget,fbsa_help_tutorials_widget',
        ),
    ),
    8 => array('unrelated_user_option' => 'keep'),
);
$controls_registry = new WidgetRegistry();
$controls_legacy = new class {
    public function set_workspace_widget_registry($registry) {}
};
(new LegacyDashboardWidgetAdapter($controls_registry))->attach($controls_legacy);
$controls_defaults = new DefaultWorkspaceLayoutRepository();
$controls_layouts = new UserWorkspaceLayoutRepository($controls_defaults, new WorkspaceLayoutValidator());
$controls_access = new WorkspaceAccessPolicy();
$controls_sync = new DashboardPreferenceSynchronizer();
$controls = new WorkspaceControls($controls_registry, $controls_layouts, $controls_defaults, $controls_access, $controls_sync);
$state = $controls->state_for_user(7);
fbsa_assert_same(4, count($state['widgets']), 'Workspace Controls exposes all four registered Dashboard widgets.');
fbsa_assert_true($state['screenOptionsAuthoritative'] === true, 'Workspace Controls declares native Screen Options authoritative.');
$saved_state = $controls->save_for_user(7, array(
    'context' => 'dashboard',
    'widgets' => array(
        array('id' => 'fbsa_website_settings_widget', 'visible' => true, 'column' => 3),
        array('id' => 'fbsa_website_steps_widget', 'visible' => false, 'column' => 3),
        array('id' => 'fbsa_help_tutorials_widget', 'visible' => true, 'column' => 4),
        array('id' => 'fbsa_plugin_setup_widget', 'visible' => true, 'column' => 4),
    ),
));
$visibility = array();
foreach ($saved_state['widgets'] as $widget_state) { $visibility[$widget_state['id']] = $widget_state['visible']; }
fbsa_assert_true($visibility['fbsa_website_steps_widget'] === false, 'Workspace Controls saves disabled widget state.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_user_options'][7]['fbsa_workspace_layouts']), 'Workspace Controls saves per-user layout data.');
fbsa_assert_same('dashboard_activity', $GLOBALS['fbsa_test_user_options'][7]['meta-box-order_dashboard']['normal'], 'Dashboard synchronization preserves WordPress core widgets.');
fbsa_assert_same('wpvivid_dashboard_widget', $GLOBALS['fbsa_test_user_options'][7]['meta-box-order_dashboard']['side'], 'Dashboard synchronization preserves third-party widgets.');
fbsa_assert_same('fbsa_website_settings_widget,fbsa_website_steps_widget', $GLOBALS['fbsa_test_user_options'][7]['meta-box-order_dashboard']['column3'], 'Dashboard synchronization applies preferred order within column 3.');
fbsa_assert_same('fbsa_help_tutorials_widget,fbsa_plugin_setup_widget', $GLOBALS['fbsa_test_user_options'][7]['meta-box-order_dashboard']['column4'], 'Dashboard synchronization applies preferred order within column 4.');
fbsa_assert_same('keep', $GLOBALS['fbsa_test_user_options'][8]['unrelated_user_option'], 'Workspace Controls do not alter another user.');
$filtered = $controls->filter_current_user_widgets($controls_registry->for_context('dashboard'), 'dashboard', $controls_registry);
$filtered_ids = array_map(function ($definition) { return $definition->id(); }, $filtered);
fbsa_assert_true(!in_array('fbsa_website_steps_widget', $filtered_ids, true), 'Disabled widgets are excluded from Dashboard registration.');
$unknown_failed = false;
try {
    $controls->save_for_user(7, array('widgets' => array(array('id' => 'unknown_widget', 'visible' => true))));
} catch (InvalidArgumentException $error) {
    $unknown_failed = true;
}
fbsa_assert_true($unknown_failed, 'Workspace Controls reject unknown widget IDs.');
$reset_state = $controls->reset_for_user(7);
fbsa_assert_same('default', $reset_state['layout']['source'], 'Workspace reset restores the default layout source.');
fbsa_assert_true(!isset($GLOBALS['fbsa_test_user_options'][7]['fbsa_workspace_layouts']), 'Workspace reset removes only the plugin-owned layout preference.');

// Capability filter and REST security.
$GLOBALS['fbsa_test_filters'] = array();
add_filter('fbsa_workspace_manage_capability', function ($capability, $user_id) { return 'edit_dashboard'; }, 10, 2);
$GLOBALS['fbsa_test_capabilities'] = array('manage_options' => false, 'edit_dashboard' => true);
fbsa_assert_true($controls_access->can_manage_layout_current_user(), 'Workspace management capability is filterable.');
$rest = new WorkspaceRestController($controls, $controls_access);
$GLOBALS['fbsa_test_rest_routes'] = array();
$rest->register_routes();
fbsa_assert_true(isset($GLOBALS['fbsa_test_rest_routes']['fb-software-ai/v1/workspace/layout']), 'Workspace REST route is registered.');
$invalid_request = new FBSA_Test_REST_Request(array('X-WP-Nonce' => 'invalid'));
$invalid_permission = $rest->permissions_check($invalid_request);
fbsa_assert_true($invalid_permission instanceof WP_Error && $invalid_permission->get_error_code() === 'fbsa_workspace_invalid_nonce', 'Workspace REST rejects an invalid nonce.');
$valid_request = new FBSA_Test_REST_Request(array('X-WP-Nonce' => 'valid-wp-rest-nonce'));
fbsa_assert_true($rest->permissions_check($valid_request) === true, 'Workspace REST accepts a valid nonce and capability.');
$GLOBALS['fbsa_test_capabilities']['manage_options'] = true; // Core widgets still require their preserved capability.
$save_request = new FBSA_Test_REST_Request(
    array('X-WP-Nonce' => 'valid-wp-rest-nonce'),
    array(),
    array('widgets' => array(
        array('id' => 'fbsa_website_steps_widget', 'visible' => true, 'column' => 3),
        array('id' => 'fbsa_website_settings_widget', 'visible' => true, 'column' => 3),
        array('id' => 'fbsa_plugin_setup_widget', 'visible' => false, 'column' => 4),
        array('id' => 'fbsa_help_tutorials_widget', 'visible' => true, 'column' => 4),
    ))
);
$rest_saved = $rest->save_layout($save_request);
$rest_visibility = array();
foreach ($rest_saved['widgets'] as $widget_state) { $rest_visibility[$widget_state['id']] = $widget_state['visible']; }
fbsa_assert_true($rest_visibility['fbsa_plugin_setup_widget'] === false, 'Workspace REST saves the authenticated user layout.');
$other_user_failed = false;
try { $controls->state_for_user(8); } catch (InvalidArgumentException $error) { $other_user_failed = true; }
fbsa_assert_true($other_user_failed, 'Workspace Controls cannot manage another user layout.');

fbsa_finish_tests();
