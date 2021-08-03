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

		$this->id            = (int) $this->id;
		$this->url           = (string) $this->url;
		$this->type          = (string) $this->type;
		$this->content       = (string) $this->content;
		$this->hash          = (string) $this->hash;
		$this->prewarmup     = (int) $this->prewarmup;
		$this->warmup_status = (int) $this->warmup_status;
		$this->media         = (string) $this->media;
		$this->modified      = false === $this->modified ? 0 : strtotime( $this->modified );
		$this->last_accessed = false === $this->last_accessed ? 0 : strtotime( $this->last_accessed );
	}
}
