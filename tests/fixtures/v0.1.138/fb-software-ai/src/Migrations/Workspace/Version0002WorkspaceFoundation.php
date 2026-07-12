<?php
/**
 * Migration 0002: seed the Workspace default layout contract.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations\Workspace;

use FBSoftwareAI\Migrations\MigrationContext;
use FBSoftwareAI\Migrations\MigrationInterface;
use FBSoftwareAI\Migrations\MigrationSnapshot;
use FBSoftwareAI\Workspace\DefaultLayoutFactory;

final class Version0002WorkspaceFoundation implements MigrationInterface {
    const MISSING = '__fbsa_workspace_defaults_missing__';

    /** @var bool */
    private $existed = false;

    /** @var mixed */
    private $previous;

    /** @return string */
    public function id() { return '0002-workspace-foundation'; }

    /** @return string */
    public function module() { return 'workspace'; }

    /** @return int */
    public function from_version() { return 0; }

    /** @return int */
    public function to_version() { return 1; }

    /** @return bool */
    public function preflight(MigrationContext $context) {
        $this->previous = get_option(DefaultLayoutFactory::OPTION_KEY, self::MISSING);
        $this->existed = $this->previous !== self::MISSING;
        return !$this->existed || is_array($this->previous);
    }

    /** @return bool */
    public function up(MigrationContext $context) {
        if ($this->existed) {
            return true;
        }
        return (bool) add_option(
            DefaultLayoutFactory::OPTION_KEY,
            DefaultLayoutFactory::option_value(),
            '',
            false
        );
    }

    /** @return bool */
    public function verify(MigrationContext $context) {
        $value = get_option(DefaultLayoutFactory::OPTION_KEY, array());
        return is_array($value)
            && isset($value['schemaVersion'])
            && (int) $value['schemaVersion'] === DefaultLayoutFactory::SCHEMA_VERSION
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
