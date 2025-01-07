<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;

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
	 * Error message
	 *
	 * @var string
	 */
	public $error_message;

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
		$this->error_message = (string) $this->error_message;
		$this->is_mobile     = (bool) $this->is_mobile;
		$this->status        = (string) $this->status;
		$this->modified      = empty( $this->modified ) ? 0 : strtotime( (string) $this->modified );
		$this->last_accessed = empty( $this->last_accessed ) ? 0 : strtotime( (string) $this->last_accessed );
	}

	/**
	 * Checks if the object has a valid LCP (Largest Contentful Paint) value.
	 *
	 * @return bool Returns true if the object's status is 'completed' and the LCP value is not empty or 'not found', false otherwise.
	 */
	public function has_lcp() {
		if ( 'completed' !== $this->status ) {
			return false;
		}

		if ( empty( $this->lcp ) ) {
			return false;
		}

		if ( 'not found' === $this->lcp ) {
			return false;
		}

		return true;
	}
}
