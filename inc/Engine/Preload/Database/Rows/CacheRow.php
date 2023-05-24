<?php

namespace WP_Rocket\Engine\Preload\Database\Rows;

use WP_Rocket\Dependencies\Database\Row;

class CacheRow extends Row {
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
	 * Is row locked
	 *
	 * @var bool
	 */
	public $is_locked;

	/**
	 * Unused variable
	 *
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * CacheRow constructor.
	 *
	 * @param object $item Current row details.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );
		$this->id            = (int) $this->id;
		$this->url           = (string) $this->url;
		$this->status        = (string) $this->status;
		$this->modified      = empty( $this->modified ) ? 0 : strtotime( $this->modified );
		$this->last_accessed = empty( $this->last_accessed ) ? 0 : strtotime( $this->last_accessed );
		$this->is_locked     = (bool) $this->is_locked;
	}
}
