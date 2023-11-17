<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Row;

use WP_Rocket\Dependencies\Database\Row;

/**
 * RUCSS UsedCSS Row.
 */
class UsedCSS extends Row {
	/**
	 * Row ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * URL
	 *
	 * @var string
	 */
	public $url;

	/**
	 * CSS
	 *
	 * @var string
	 */
	public $css;

	/**
	 * Hash storage value
	 *
	 * @var string
	 */
	public $hash;

	/**
	 * Error code
	 *
	 * @var string
	 */
	public $error_code;

	/**
	 * Error message
	 *
	 * @var string
	 */
	public $error_message;

	/**
	 * Number of retries
	 *
	 * @var int
	 */
	public $retries;

	/**
	 * Is CSS for mobile
	 *
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * Job ID
	 *
	 * @var string
	 */
	public $job_id;

	/**
	 * Job queue name
	 *
	 * @var string
	 */
	public $queue_name;

	/**
	 * Status
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Last modified time
	 *
	 * @var int
	 */
	public $modified;

	/**
	 * Last accessed time
	 *
	 * @var int
	 */
	public $last_accessed;

	/**
	 * Unused variable
	 *
	 * @var string
	 */
	public $unprocessedcss;

	/**
	 * Submitted date
	 *
	 * @var int
	 */
	public $submitted_at;

	/**
	 * Tells when the retry has to be processed
	 *
	 * @var int
	 */
	public $next_retry_time;

	/**
	 * UsedCSS constructor.
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		// Set the type of each column, and prepare.
		$this->id              = (int) $this->id;
		$this->url             = (string) $this->url;
		$this->css             = (string) $this->css;
		$this->hash            = (string) $this->hash;
		$this->error_code      = (string) $this->error_code;
		$this->error_message   = (string) $this->error_message;
		$this->retries         = (int) $this->retries;
		$this->is_mobile       = (bool) $this->is_mobile;
		$this->job_id          = (string) $this->job_id;
		$this->queue_name      = (string) $this->queue_name;
		$this->status          = (string) $this->status;
		$this->modified        = empty( $this->modified ) ? 0 : strtotime( $this->modified );
		$this->last_accessed   = empty( $this->last_accessed ) ? 0 : strtotime( $this->last_accessed );
		$this->submitted_at    = empty( $this->submitted_at ) ? 0 : strtotime( $this->submitted_at );
		$this->next_retry_time = empty( $this->next_retry_time ) ? 0 : strtotime( $this->next_retry_time );
	}
}
