<?php

namespace WP_Rocket\Engine\Preload\Database\Rows;

use WP_Rocket\Dependencies\Database\Row;

class RocketCacheRow extends Row {

	/**
	 * RocketCacheRow constructor.
	 *
	 * @param object $item Current row details.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id            = (int) $this->id;
		$this->is_mobile     = (bool) $this->is_mobile;
		$this->url           = (string) $this->url;
		$this->status        = (string) $this->status;
		$this->modified      = false === $this->modified ? 0 : strtotime( $this->modified );
		$this->last_accessed = false === $this->last_accessed ? 0 : strtotime( $this->last_accessed );
	}
}
