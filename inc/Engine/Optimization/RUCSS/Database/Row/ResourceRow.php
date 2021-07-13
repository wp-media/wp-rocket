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
		$this->content       = $this->get_decoded_content();
		$this->hash          = (string) $this->hash;
		$this->prewarmup     = (int) $this->prewarmup;
		$this->warmup_status = (int) $this->warmup_status;
		$this->media         = (string) $this->media;
		$this->modified      = false === $this->modified ? 0 : strtotime( $this->modified );
		$this->last_accessed = false === $this->last_accessed ? 0 : strtotime( $this->last_accessed );
	}

	/**
	 * Decode the content if it's encoded.
	 *
	 * @return string
	 */
	private function get_decoded_content(): string {
		// We used strict decode to return false in case the data is not encoded (backward compatibility).
		$decoded_content = base64_decode( $this->content, true );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( ! $decoded_content ) {
			return $decoded_content;
		}

		return (string) $this->content;
	}

}
