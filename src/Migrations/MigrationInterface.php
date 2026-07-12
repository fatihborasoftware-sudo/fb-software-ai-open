<?php
/**
 * Immutable migration contract.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

interface MigrationInterface {
    /** @return string */
    public function id();

    /** @return string */
    public function module();

    /** @return int */
    public function from_version();

    /** @return int */
    public function to_version();

    /** @return bool */
    public function preflight(MigrationContext $context);

    /** @return bool */
    public function up(MigrationContext $context);

    /** @return bool */
    public function verify(MigrationContext $context);

    /** @return void */
    public function recover(MigrationContext $context, MigrationSnapshot $snapshot);
}
