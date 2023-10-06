<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Row;

use WP_Rocket\Dependencies\Database\Row;

class AboveTheFold extends Row {
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
	 * LCP
	 *
	 * @var string
	 */
	public $lcp;

	/**
	 * Viewport
	 *
	 * @var string
	 */
	public $viewport;

	/**
	 * Is CSS for mobile
	 *
	 * @var bool
	 */
	public $is_mobile;

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
	 * Submitted time
	 *
	 * @var int
	 */
	public $submitted_at;

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
	 * Constructor.
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		// Set the type of each column, and prepare.
		$this->id            = (int) $this->id;
		$this->url           = (string) $this->url;
		$this->lcp           = (string) $this->lcp;
		$this->viewport      = (string) $this->viewport;
		$this->error_code    = (string) $this->error_code;
		$this->error_message = (string) $this->error_message;
		$this->retries       = (int) $this->retries;
		$this->is_mobile     = (bool) $this->is_mobile;
		$this->job_id        = (string) $this->job_id;
		$this->queue_name    = (string) $this->queue_name;
		$this->status        = (string) $this->status;
		$this->submitted_at  = empty( $this->submitted_at ) ? 0 : strtotime( $this->submitted_at );
		$this->modified      = empty( $this->modified ) ? 0 : strtotime( $this->modified );
		$this->last_accessed = empty( $this->last_accessed ) ? 0 : strtotime( $this->last_accessed );
	}
}
