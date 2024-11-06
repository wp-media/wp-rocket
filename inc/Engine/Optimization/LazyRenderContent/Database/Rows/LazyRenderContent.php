<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;

class LazyRenderContent extends Row {
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
	 * Is for mobile
	 *
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * Below the fold
	 *
	 * @var string
	 */
	public $below_the_fold;

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
	 * Created time
	 *
	 * @var int
	 */
	public $created_at;

	/**
	 * Constructor.
	 *
	 * @param mixed $item Object Row.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		// Set the type of each column, and prepare.
		$this->id             = (int) $this->id;
		$this->url            = (string) $this->url;
		$this->is_mobile      = (bool) $this->is_mobile;
		$this->below_the_fold = (string) $this->below_the_fold;
		$this->error_message  = (string) $this->error_message;
		$this->status         = (string) $this->status;
		$this->modified       = empty( $this->modified ) ? 0 : strtotime( (string) $this->modified );
		$this->last_accessed  = empty( $this->last_accessed ) ? 0 : strtotime( (string) $this->last_accessed );
		$this->created_at     = empty( $this->created_at ) ? 0 : strtotime( (string) $this->created_at );
	}

	/**
	 * Checks if the object has a valid LRC (Lazy Render Content) value.
	 *
	 * @return bool Returns true if the object's status is 'completed' and the Below the fold value is not empty or '[]', false otherwise.
	 */
	public function has_lrc() {
		if ( 'completed' !== $this->status ) {
			return false;
		}

		if ( empty( $this->below_the_fold ) ) {
			return false;
		}

		if ( '[]' === $this->below_the_fold ) {
			return false;
		}

		return true;
	}
}
