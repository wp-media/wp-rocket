<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Database\Row;

use WP_Rocket\Dependencies\Database\Row;

class ResourceRow extends Row {

	/**
	 * ResourceRow constructor.
	 *
	 * @param object $item Current row details.
	 */
	public function __construct( $item ) {
		parent::__construct( $item );

		$this->id               = (int) $this->id;
		$this->url              = (string) $this->url;
		$this->type             = (string) $this->type;
		$this->content          = (string) $this->content;
		$this->hash             = (string) $this->hash;
		$this->created          = (string) $this->created;
		$this->modified         = (string) $this->modified;
		$this->last_accessed    = false === $this->date ? 0 : strtotime( $this->last_accessed );
	}

}
