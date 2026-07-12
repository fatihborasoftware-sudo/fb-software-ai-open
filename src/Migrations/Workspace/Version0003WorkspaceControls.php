<?php
/**
 * Migration 0003: mark the default Workspace contract as controls-ready.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations\Workspace;

use FBSoftwareAI\Migrations\MigrationContext;
use FBSoftwareAI\Migrations\MigrationInterface;
use FBSoftwareAI\Migrations\MigrationSnapshot;
use FBSoftwareAI\Workspace\DefaultLayoutFactory;

final class Version0003WorkspaceControls implements MigrationInterface {
    const MISSING = '__fbsa_workspace_controls_missing__';

    /** @var mixed */
    private $previous;

    /** @var bool */
    private $existed = false;

    /** @return string */
    public function id() { return '0003-workspace-controls'; }

    /** @return string */
    public function module() { return 'workspace'; }

    /** @return int */
    public function from_version() { return 1; }

    /** @return int */
    public function to_version() { return 2; }

    /** @return bool */
    public function preflight(MigrationContext $context) {
        $this->previous = get_option(DefaultLayoutFactory::OPTION_KEY, self::MISSING);
        $this->existed = $this->previous !== self::MISSING;
        return !$this->existed || is_array($this->previous);
    }

    /** @return bool */
    public function up(MigrationContext $context) {
        $value = $this->existed && is_array($this->previous)
            ? $this->previous
            : DefaultLayoutFactory::option_value();
        $value['controlsVersion'] = 1;
        if (!$this->existed) {
            return (bool) add_option(DefaultLayoutFactory::OPTION_KEY, $value, '', false);
        }
        update_option(DefaultLayoutFactory::OPTION_KEY, $value, false);
        return true;
    }

    /** @return bool */
    public function verify(MigrationContext $context) {
        $value = get_option(DefaultLayoutFactory::OPTION_KEY, array());
        return is_array($value)
            && isset($value['controlsVersion'])
            && (int) $value['controlsVersion'] === 1
            && isset($value['layouts']['dashboard'])
            && is_array($value['layouts']['dashboard']);
    }

    /** @return void */
    public function recover(MigrationContext $context, MigrationSnapshot $snapshot) {
        if ($this->existed) {
            update_option(DefaultLayoutFactory::OPTION_KEY, $this->previous, false);
            return;
        }
        delete_option(DefaultLayoutFactory::OPTION_KEY);
    }
}
