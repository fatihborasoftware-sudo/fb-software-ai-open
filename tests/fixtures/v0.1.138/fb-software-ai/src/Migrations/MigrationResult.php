<?php
/**
 * Immutable result of a migration request.
 *
 * @package FBSoftwareAI
 */

namespace FBSoftwareAI\Migrations;

final class MigrationResult {
    const STATUS_APPLIED = 'applied';
    const STATUS_NOOP = 'noop';
    const STATUS_FAILED = 'failed';
    const STATUS_LOCKED = 'locked';

    /** @var string */
    private $status;

    /** @var string */
    private $request_id;

    /** @var string[] */
    private $migration_ids;

    /** @var string */
    private $message;

    public function __construct($status, $request_id, array $migration_ids = array(), $message = '') {
        $this->status = (string) $status;
        $this->request_id = (string) $request_id;
        $this->migration_ids = array_values($migration_ids);
        $this->message = (string) $message;
    }

    /** @return string */
    public function status() {
        return $this->status;
    }

    /** @return bool */
    public function successful() {
        return in_array($this->status, array(self::STATUS_APPLIED, self::STATUS_NOOP), true);
    }

    /** @return bool */
    public function changed() {
        return $this->status === self::STATUS_APPLIED;
    }

    /** @return string */
    public function request_id() {
        return $this->request_id;
    }

    /** @return string[] */
    public function migration_ids() {
        return $this->migration_ids;
    }

    /** @return string */
    public function message() {
        return $this->message;
    }

    /** @return array */
    public function to_array() {
        return array(
            'status'        => $this->status,
            'request_id'    => $this->request_id,
            'migration_ids' => $this->migration_ids,
            'message'       => $this->message,
        );
    }
}
