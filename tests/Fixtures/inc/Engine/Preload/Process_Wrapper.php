<?php
// phpcs:ignoreFile
namespace WP_Rocket\Tests\Integration\Fixtures\inc\Engine\Preload;

use WP_Rocket\Engine\Preload\FullProcess;

/**
 * Wrapper class used to test the results.
 */
class Process_Wrapper extends FullProcess {
	private $generatedKey;

	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
			$this->generatedKey = $key;
		} else {
			$this->generatedKey = null;
		}

		return $this;
	}

	public function getGeneratedKey() {
		return $this->generatedKey;
	}
}
