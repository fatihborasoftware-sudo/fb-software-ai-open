<?php
/**
 * Per-user, per-site Workspace layout repository.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use InvalidArgumentException;

final class UserWorkspaceLayoutRepository implements WorkspaceLayoutRepositoryInterface {
    const USER_OPTION_KEY = 'fbsa_workspace_layouts';

    /** @var DefaultWorkspaceLayoutRepository */
    private $defaults;

    /** @var WorkspaceLayoutValidator */
    private $validator;

    public function __construct(DefaultWorkspaceLayoutRepository $defaults, WorkspaceLayoutValidator $validator) {
        $this->defaults = $defaults;
        $this->validator = $validator;
    }

    /** @return array<string,mixed>|null */
    public function get_for_user($user_id, $context) {
        $user_id = (int) $user_id;
        $context = (string) $context;
        if ($user_id <= 0) {
            return null;
        }

        $stored = get_user_option(self::USER_OPTION_KEY, $user_id);
        $layout = is_array($stored) && isset($stored[$context]) && is_array($stored[$context])
            ? $stored[$context]
            : $this->defaults->get($context);

        if (!is_array($layout)) {
            return null;
        }

        try {
            $layout = $this->validator->normalize($layout);
        } catch (InvalidArgumentException $error) {
            $layout = $this->defaults->get($context);
            if (!is_array($layout)) {
                return null;
            }
            $layout = $this->validator->normalize($layout);
        }

        if (function_exists('apply_filters')) {
            $filtered = apply_filters('fbsa_workspace_layout_loaded', $layout, $user_id, $context);
            if (is_array($filtered)) {
                $filtered['context'] = $context;
                $layout = $this->validator->normalize($filtered);
            }
        }

        return $layout;
    }

    /** @return array<string,mixed> */
    public function save_for_user($user_id, array $layout) {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            throw new InvalidArgumentException('A valid WordPress user is required to save a Workspace layout.');
        }

        $layout['source'] = 'user';
        $layout = $this->validator->normalize($layout);
        $stored = get_user_option(self::USER_OPTION_KEY, $user_id);
        $stored = is_array($stored) ? $stored : array();
        $stored[$layout['context']] = $layout;
        update_user_option($user_id, self::USER_OPTION_KEY, $stored, false);

        if (function_exists('do_action')) {
            do_action('fbsa_workspace_layout_saved', $layout, $user_id, $layout['context']);
        }

        return $layout;
    }

    /** @return bool */
    public function reset_for_user($user_id, $context) {
        $user_id = (int) $user_id;
        $context = (string) $context;
        if ($user_id <= 0) {
            return false;
        }
        $stored = get_user_option(self::USER_OPTION_KEY, $user_id);
        if (!is_array($stored) || !array_key_exists($context, $stored)) {
            return false;
        }
        unset($stored[$context]);
        if ($stored === array()) {
            delete_user_option($user_id, self::USER_OPTION_KEY, false);
        } else {
            update_user_option($user_id, self::USER_OPTION_KEY, $stored, false);
        }

        if (function_exists('do_action')) {
            do_action('fbsa_workspace_layout_reset', $user_id, $context);
        }
        return true;
    }
}
