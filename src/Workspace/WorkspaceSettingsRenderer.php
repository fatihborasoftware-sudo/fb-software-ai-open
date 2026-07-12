<?php
/**
 * Adds Workspace controls to the existing FB Software AI settings screen.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class WorkspaceSettingsRenderer {
    /** @var WorkspaceControls */
    private $controls;

    /** @var WorkspaceAccessPolicy */
    private $access;

    /** @var string */
    private $plugin_file;

    public function __construct(WorkspaceControls $controls, WorkspaceAccessPolicy $access, $plugin_file) {
        $this->controls = $controls;
        $this->access = $access;
        $this->plugin_file = (string) $plugin_file;
    }

    /** @return void */
    public function register() {
        add_action('fbsa_settings_tabs', array($this, 'render_tab'));
        add_action('fbsa_settings_panels', array($this, 'render_panel'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'), 20);
    }

    /** @return void */
    public function render_tab() {
        if (!$this->access->can_manage_layout_current_user()) {
            return;
        }
        ?>
        <button type="button" class="fbsa-settings-tab" id="fbsa-settings-tab-workspace" role="tab" aria-selected="false" aria-controls="fbsa-settings-panel-workspace" data-fbsa-settings-tab="workspace"><span class="dashicons dashicons-screenoptions" aria-hidden="true"></span><?php echo esc_html__('Workspace', 'fb-software-ai'); ?></button>
        <?php
    }

    /** @return void */
    public function render_panel() {
        if (!$this->access->can_manage_layout_current_user()) {
            return;
        }
        $state = $this->controls->state_for_user(get_current_user_id());
        $widgets = isset($state['widgets']) && is_array($state['widgets']) ? $state['widgets'] : array();
        ?>
        <div id="fbsa-settings-panel-workspace" class="fbsa-settings-tab-panel" role="tabpanel" aria-labelledby="fbsa-settings-tab-workspace" data-fbsa-settings-panel="workspace" hidden>
            <section class="fbsa-settings-card fbsa-workspace-controls" data-fbsa-workspace-controls>
                <div class="fbsa-settings-card__header fbsa-workspace-controls__header">
                    <div>
                        <span class="fbsa-settings-eyebrow"><?php echo esc_html__('Workspace', 'fb-software-ai'); ?></span>
                        <h2><?php echo esc_html__('Dashboard Widget Controls', 'fb-software-ai'); ?></h2>
                        <p class="fbsa-muted"><?php echo esc_html__('Choose which FB Software AI widgets are available and set their preferred order. WordPress Screen Options remains authoritative for Dashboard visibility.', 'fb-software-ai'); ?></p>
                    </div>
                    <a class="button fbsa-secondary-button" href="<?php echo esc_url(admin_url('index.php')); ?>"><?php echo esc_html__('Open WordPress Dashboard', 'fb-software-ai'); ?></a>
                </div>

                <div class="fbsa-workspace-notice" data-fbsa-workspace-notice role="status" aria-live="polite" hidden></div>

                <ol class="fbsa-workspace-widget-list" data-fbsa-workspace-widget-list>
                    <?php foreach ($widgets as $index => $widget) : ?>
                        <li class="fbsa-workspace-widget-card" draggable="true" data-fbsa-workspace-widget data-widget-id="<?php echo esc_attr($widget['id']); ?>" data-widget-column="<?php echo esc_attr((int) $widget['column']); ?>">
                            <span class="fbsa-workspace-widget-card__drag dashicons dashicons-move" aria-hidden="true"></span>
                            <div class="fbsa-workspace-widget-card__content">
                                <strong><?php echo esc_html($widget['title']); ?></strong>
                                <p><?php echo esc_html($widget['description']); ?></p>
                                <small><span data-fbsa-workspace-position data-column-label="<?php echo esc_attr($this->column_label($widget['column'])); ?>"><?php echo esc_html(sprintf(__('%1$s · Position %2$d', 'fb-software-ai'), $this->column_label($widget['column']), $this->position_in_column($widgets, $index))); ?></span> · <code><?php echo esc_html($widget['id']); ?></code></small>
                            </div>
                            <label class="fbsa-workspace-switch">
                                <input type="checkbox" data-fbsa-workspace-visible <?php checked(!empty($widget['visible'])); ?> <?php disabled(empty($widget['removable'])); ?> />
                                <span><?php echo esc_html__('Enabled', 'fb-software-ai'); ?></span>
                            </label>
                            <div class="fbsa-workspace-widget-card__actions" aria-label="<?php echo esc_attr__('Widget order controls', 'fb-software-ai'); ?>">
                                <button type="button" class="button button-small" data-fbsa-workspace-move="up" aria-label="<?php echo esc_attr__('Move widget up', 'fb-software-ai'); ?>"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
                                <button type="button" class="button button-small" data-fbsa-workspace-move="down" aria-label="<?php echo esc_attr__('Move widget down', 'fb-software-ai'); ?>"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>

                <div class="fbsa-workspace-controls__footer">
                    <p class="fbsa-muted"><?php echo esc_html__('These preferences are saved separately for each WordPress user. Third-party Dashboard widgets are never removed or reordered by FB Software AI.', 'fb-software-ai'); ?></p>
                    <div class="fbsa-settings-actions">
                        <button type="button" class="button fbsa-secondary-button" data-fbsa-workspace-reset><?php echo esc_html__('Reset to Default', 'fb-software-ai'); ?></button>
                        <button type="button" class="button button-primary fbsa-premium-button" data-fbsa-workspace-save><?php echo esc_html__('Save Workspace', 'fb-software-ai'); ?></button>
                    </div>
                </div>
            </section>
        </div>
        <?php
    }

    /** @return void */
    public function enqueue_assets($hook_suffix = '') {
        if (!$this->is_settings_page() || !$this->access->can_manage_layout_current_user()) {
            return;
        }
        $plugin_url = plugin_dir_url($this->plugin_file);
        $version = class_exists('FBSoftwareAI\\Core\\Version') ? \FBSoftwareAI\Core\Version::plugin() : '0.1.139';
        wp_enqueue_style(
            'fbsa-workspace-settings',
            $plugin_url . 'assets/css/workspace-settings.css',
            array('fbsa-settings'),
            $version
        );
        wp_enqueue_script(
            'fbsa-workspace-settings',
            $plugin_url . 'assets/js/workspace-settings.js',
            array(),
            $version,
            true
        );
        wp_localize_script('fbsa-workspace-settings', 'fbsaWorkspaceControls', array(
            'endpoint' => esc_url_raw(rest_url(WorkspaceRestController::NAMESPACE . WorkspaceRestController::ROUTE)),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => array(
                'saving' => __('Saving Workspace…', 'fb-software-ai'),
                'saved' => __('Workspace saved. Refresh the Dashboard to see the preferred order.', 'fb-software-ai'),
                'resetting' => __('Resetting Workspace…', 'fb-software-ai'),
                'reset' => __('Workspace reset to the default layout.', 'fb-software-ai'),
                'error' => __('The Workspace could not be updated. Please try again.', 'fb-software-ai'),
                'confirmReset' => __('Reset your FB Software AI Workspace to the default layout?', 'fb-software-ai'),
                'positionInColumn' => __('%1$s · Position %2$d', 'fb-software-ai'),
            ),
        ));
    }


    /** @return string */
    private function column_label($column) {
        $labels = array(
            1 => __('Main column', 'fb-software-ai'),
            2 => __('Side column', 'fb-software-ai'),
            3 => __('Column 3', 'fb-software-ai'),
            4 => __('Column 4', 'fb-software-ai'),
        );
        $column = (int) $column;
        return isset($labels[$column]) ? $labels[$column] : __('Dashboard column', 'fb-software-ai');
    }

    /** @return int */
    private function position_in_column(array $widgets, $index) {
        $index = (int) $index;
        if (!isset($widgets[$index])) {
            return 1;
        }
        $column = isset($widgets[$index]['column']) ? (int) $widgets[$index]['column'] : 1;
        $position = 0;
        foreach ($widgets as $candidate_index => $candidate) {
            if ($candidate_index > $index) {
                break;
            }
            if (isset($candidate['column']) && (int) $candidate['column'] === $column) {
                $position++;
            }
        }
        return max(1, $position);
    }

    /** @return bool */
    private function is_settings_page() {
        return function_exists('is_admin')
            && is_admin()
            && isset($_GET['page'])
            && function_exists('sanitize_key')
            && sanitize_key(wp_unslash($_GET['page'])) === 'fb-software-ai';
    }
}
