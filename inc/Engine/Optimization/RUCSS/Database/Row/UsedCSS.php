<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Row;

use WP_Rocket\Dependencies\Database\Row;

/**
 * RUCSS UsedCSS Row.
 */
class UsedCSS extends Row {
	private $id;
	private $url;
	private $css;
	private $hash;
	private $error_code;
	private $error_message;
	private $retries;
	private $is_mobile;
	private $job_id;
	private $queue_name;
	private $status;
	private $modified;
	private $last_accessed;

	/**
	 * UsedCSS constructor.
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		// Set the type of each column, and prepare.
		$this->id            = (int) $this->id;
		$this->url           = (string) $this->url;
		$this->css           = (string) $this->css;
		$this->hash          = (string) $this->hash;
		$this->error_code    = (string) $this->error_code;
		$this->error_message = (string) $this->error_message;
		$this->retries       = (int) $this->retries;
		$this->is_mobile     = (bool) $this->is_mobile;
		$this->job_id        = (string) $this->job_id;
		$this->queue_name    = (string) $this->queue_name;
		$this->status        = (string) $this->status;
		$this->modified      = empty( $this->modified ) ? 0 : strtotime( $this->modified );
		$this->last_accessed = empty( $this->last_accessed ) ? 0 : strtotime( $this->last_accessed );
	}
}
