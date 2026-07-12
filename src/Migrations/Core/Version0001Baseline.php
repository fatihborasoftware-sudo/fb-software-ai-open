<?php
/**
 * Migration 0001: establish the core schema-version baseline only.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations\Core;

use FBSoftwareAI\Migrations\MigrationContext;
use FBSoftwareAI\Migrations\MigrationInterface;
use FBSoftwareAI\Migrations\MigrationSnapshot;

final class Version0001Baseline implements MigrationInterface {
    /** @return string */
    public function id() {
        return '0001-core-baseline';
    }

    /** @return string */
    public function module() {
        return 'core';
    }

    /** @return int */
    public function from_version() {
        return 0;
    }

    /** @return int */
    public function to_version() {
        return 1;
    }

    /** @return bool */
    public function preflight(MigrationContext $context) {
        $context->set('0001.settings.before', $this->checksum($context->settings()->all()));
        return true;
    }

    /**
     * No legacy data is modified. The runner advances the core schema only
     * after verification succeeds.
     *
     * @return bool
     */
    public function up(MigrationContext $context) {
        return true;
    }

    /** @return bool */
    public function verify(MigrationContext $context) {
        $before = (string) $context->get('0001.settings.before', '');
        $after = $this->checksum($context->settings()->all());
        return $before !== '' && hash_equals($before, $after);
    }

    /** @return void */
    public function recover(MigrationContext $context, MigrationSnapshot $snapshot) {
        $context->schemas()->restore($snapshot->schema_snapshot());
    }

    /**
     * @param mixed $value Value to hash.
     * @return string
     */
    private function checksum($value) {
        return hash('sha256', serialize($value));
    }
}
