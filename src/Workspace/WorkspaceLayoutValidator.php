<?php
/**
 * Validation and normalization for Workspace layouts.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Workspace;

use InvalidArgumentException;

final class WorkspaceLayoutValidator {
    /** @var string[] */
    private $contexts = array('dashboard', 'workspace', 'frontend', 'customizer');

    /**
     * @param array<string,mixed> $layout Layout candidate.
     * @return array<string,mixed>
     */
    public function normalize(array $layout) {
        $context = isset($layout['context']) ? (string) $layout['context'] : '';
        if (!in_array($context, $this->contexts, true)) {
            throw new InvalidArgumentException('Invalid FB Software AI Workspace context.');
        }

        $widgets = isset($layout['widgets']) && is_array($layout['widgets']) ? $layout['widgets'] : array();
        $normalized_widgets = array();
        $seen = array();
        foreach ($widgets as $widget) {
            if (!is_array($widget)) {
                continue;
            }
            $id = isset($widget['id']) ? (string) $widget['id'] : '';
            if (!preg_match('/^[a-z][a-z0-9_.-]{2,190}$/', $id) || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $normalized_widgets[] = array(
                'id'        => $id,
                'visible'   => isset($widget['visible']) ? (bool) $widget['visible'] : true,
                'column'    => max(1, min(12, isset($widget['column']) ? (int) $widget['column'] : 1)),
                'order'     => max(0, isset($widget['order']) ? (int) $widget['order'] : count($normalized_widgets)),
                'collapsed' => isset($widget['collapsed']) ? (bool) $widget['collapsed'] : false,
                'width'     => $this->nullable_dimension(isset($widget['width']) ? $widget['width'] : null),
                'height'    => $this->nullable_dimension(isset($widget['height']) ? $widget['height'] : null),
                'settings'  => isset($widget['settings']) && is_array($widget['settings']) ? $this->sanitize_settings($widget['settings']) : array(),
            );
        }

        usort($normalized_widgets, function (array $left, array $right) {
            if ($left['column'] === $right['column']) {
                if ($left['order'] === $right['order']) {
                    return strcmp($left['id'], $right['id']);
                }
                return $left['order'] <=> $right['order'];
            }
            return $left['column'] <=> $right['column'];
        });

        return array(
            'schemaVersion' => DefaultLayoutFactory::SCHEMA_VERSION,
            'context'       => $context,
            'layoutId'      => isset($layout['layoutId']) ? $this->sanitize_text($layout['layoutId']) : '',
            'roleTemplate'  => isset($layout['roleTemplate']) && $layout['roleTemplate'] !== null ? $this->sanitize_text($layout['roleTemplate']) : null,
            'widgets'       => $normalized_widgets,
            'updatedAt'     => $this->current_gmt_time(),
            'source'        => isset($layout['source']) && in_array($layout['source'], array('default', 'role', 'user', 'imported', 'legacy_adapter'), true)
                ? $layout['source']
                : 'user',
        );
    }

    /** @return int|null */
    private function nullable_dimension($value) {
        if ($value === null || $value === '') {
            return null;
        }
        return max(0, (int) $value);
    }

    /** @return string */
    private function sanitize_text($value) {
        if (function_exists('sanitize_text_field')) {
            return (string) sanitize_text_field((string) $value);
        }
        return trim(strip_tags((string) $value));
    }

    /** @return array<string,mixed> */
    private function sanitize_settings(array $settings, $depth = 0) {
        if ($depth >= 5) {
            return array();
        }
        $clean = array();
        foreach ($settings as $key => $value) {
            $clean_key = function_exists('sanitize_key') ? sanitize_key((string) $key) : preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $key));
            if ($clean_key === '') {
                continue;
            }
            if (is_array($value)) {
                $clean[$clean_key] = $this->sanitize_settings($value, $depth + 1);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $clean[$clean_key] = $value;
            } else {
                $clean[$clean_key] = $this->sanitize_text($value);
            }
        }
        return $clean;
    }

    /** @return string */
    private function current_gmt_time() {
        return function_exists('current_time') ? (string) current_time('mysql', true) : gmdate('Y-m-d H:i:s');
    }
}
