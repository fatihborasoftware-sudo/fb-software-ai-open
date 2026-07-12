<?php
/**
 * REST API for the authenticated user's Workspace layout.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use InvalidArgumentException;

final class WorkspaceRestController {
    const NAMESPACE = 'fb-software-ai/v1';
    const ROUTE = '/workspace/layout';

    /** @var WorkspaceControls */
    private $controls;

    /** @var WorkspaceAccessPolicy */
    private $access;

    public function __construct(WorkspaceControls $controls, WorkspaceAccessPolicy $access) {
        $this->controls = $controls;
        $this->access = $access;
    }

    /** @return void */
    public function register_routes() {
        register_rest_route(self::NAMESPACE, self::ROUTE, array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_layout'),
                'permission_callback' => array($this, 'permissions_check'),
            ),
            array(
                'methods' => 'PUT',
                'callback' => array($this, 'save_layout'),
                'permission_callback' => array($this, 'permissions_check'),
            ),
            array(
                'methods' => 'DELETE',
                'callback' => array($this, 'reset_layout'),
                'permission_callback' => array($this, 'permissions_check'),
            ),
        ));
    }

    /**
     * @param mixed $request REST request.
     * @return bool|mixed
     */
    public function permissions_check($request) {
        if (!$this->access->can_manage_layout_current_user()) {
            return $this->error('fbsa_workspace_forbidden', __('You are not allowed to manage this Workspace.', 'fb-software-ai'), 403);
        }

        $nonce = '';
        if (is_object($request) && method_exists($request, 'get_header')) {
            $nonce = (string) $request->get_header('X-WP-Nonce');
        }
        if ($nonce === '' && is_object($request) && method_exists($request, 'get_param')) {
            $nonce = (string) $request->get_param('_wpnonce');
        }
        if (!function_exists('wp_verify_nonce') || !wp_verify_nonce($nonce, 'wp_rest')) {
            return $this->error('fbsa_workspace_invalid_nonce', __('The Workspace security token is invalid or expired.', 'fb-software-ai'), 403);
        }
        return true;
    }

    /** @return mixed */
    public function get_layout($request) {
        return $this->respond($this->controls->state_for_user(get_current_user_id()));
    }

    /** @return mixed */
    public function save_layout($request) {
        try {
            $payload = is_object($request) && method_exists($request, 'get_json_params')
                ? $request->get_json_params()
                : array();
            if (!is_array($payload)) {
                throw new InvalidArgumentException(__('The Workspace request body must be a JSON object.', 'fb-software-ai'));
            }
            return $this->respond($this->controls->save_for_user(get_current_user_id(), $payload));
        } catch (InvalidArgumentException $error) {
            return $this->error('fbsa_workspace_invalid_layout', $error->getMessage(), 400);
        }
    }

    /** @return mixed */
    public function reset_layout($request) {
        try {
            return $this->respond($this->controls->reset_for_user(get_current_user_id()));
        } catch (InvalidArgumentException $error) {
            return $this->error('fbsa_workspace_reset_failed', $error->getMessage(), 400);
        }
    }

    /** @return mixed */
    private function respond($data) {
        return function_exists('rest_ensure_response') ? rest_ensure_response($data) : $data;
    }

    /** @return mixed */
    private function error($code, $message, $status) {
        if (class_exists('WP_Error')) {
            return new \WP_Error((string) $code, (string) $message, array('status' => (int) $status));
        }
        return array('code' => (string) $code, 'message' => (string) $message, 'status' => (int) $status);
    }
}
