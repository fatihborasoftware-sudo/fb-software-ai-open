<?php
/**
 * Internal PSR-4 style autoloader for FB Software AI.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Core;

final class Autoloader {
    /** @var string */
    private static $base_directory = '';

    /** @var bool */
    private static $registered = false;

    /**
     * Register the internal namespace loader.
     *
     * @param string $base_directory Absolute path to the src directory.
     * @return void
     */
    public static function register($base_directory) {
        if (self::$registered) {
            return;
        }

        self::$base_directory = rtrim((string) $base_directory, '/\\') . DIRECTORY_SEPARATOR;
        spl_autoload_register(array(__CLASS__, 'autoload'));
        self::$registered = true;
    }

    /**
     * Load an FB Software AI class from the src directory.
     *
     * @param string $class Fully qualified class name.
     * @return void
     */
    public static function autoload($class) {
        $prefix = 'FBSoftwareAI\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        if ($relative === '' || strpos($relative, '..') !== false) {
            return;
        }

        $file = self::$base_directory . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
        if (is_readable($file)) {
            require_once $file;
        }
    }
}
