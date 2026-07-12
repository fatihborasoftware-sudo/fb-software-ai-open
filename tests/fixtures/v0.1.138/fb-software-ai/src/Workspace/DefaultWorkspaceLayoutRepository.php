<?php
/**
 * Repository for site default Workspace layouts.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

final class DefaultWorkspaceLayoutRepository {
    /** @return array<string,mixed> */
    public function all() {
        $value = get_option(DefaultLayoutFactory::OPTION_KEY, DefaultLayoutFactory::option_value());
        if (!is_array($value) || !isset($value['layouts']) || !is_array($value['layouts'])) {
            return DefaultLayoutFactory::option_value();
        }
        return $value;
    }

    /** @return array<string,mixed>|null */
    public function get($context) {
        $all = $this->all();
        $context = (string) $context;
        return isset($all['layouts'][$context]) && is_array($all['layouts'][$context])
            ? $all['layouts'][$context]
            : null;
    }
}
