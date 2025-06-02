<?php

namespace WP_Rocket\Engine\Media\PreloadFonts\Database\Rows;

use WP_Rocket\Dependencies\BerlinDB\Database\Row;

class PreloadFonts extends Row {
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
	 * Fonts
	 *
	 * @var string
	 */
	public $fonts;

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
		$this->id            = (int) $this->id;
		$this->url           = (string) $this->url;
		$this->is_mobile     = (bool) $this->is_mobile;
		$this->fonts         = (string) $this->fonts;
		$this->status        = (string) $this->status;
		$this->error_message = (string) $this->error_message;
		$this->modified      = empty( $this->modified ) ? 0 : strtotime( (string) $this->modified );
		$this->last_accessed = empty( $this->last_accessed ) ? 0 : strtotime( (string) $this->last_accessed );
		$this->created_at    = empty( $this->created_at ) ? 0 : strtotime( (string) $this->created_at );
	}

	/**
	 * Checks if the object has a valid Preload Fonts value.
	 *
	 * @return bool Returns true if the object's status is 'completed' and the fonts value is not empty or '[]', false otherwise.
	 */
	public function has_preload_fonts() {
		if ( 'completed' !== $this->status ) {
			return false;
		}

		if ( empty( $this->fonts ) ) {
			return false;
		}

		if ( '[]' === $this->fonts ) {
			return false;
		}

		return true;
	}
}
