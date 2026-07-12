<?php
/**
 * Contract for per-user Workspace layout storage.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

interface WorkspaceLayoutRepositoryInterface {
    /** @return array<string,mixed>|null */
    public function get_for_user($user_id, $context);

    /** @return array<string,mixed> */
    public function save_for_user($user_id, array $layout);

    /** @return bool */
    public function reset_for_user($user_id, $context);
}
