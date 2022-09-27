<?php

namespace WP_Rocket\Engine\Preload\Database\Rows;

use WP_Rocket\Dependencies\Database\Row;

class CacheRow extends Row {

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
		$this->modified      = false === $this->modified ? 0 : strtotime( $this->modified );
		$this->last_accessed = false === $this->last_accessed ? 0 : strtotime( $this->last_accessed );
		$this->is_locked     = (bool) $this->is_locked;
	}
}
