<?php
// phpcs:ignoreFile
namespace WP_Rocket\Tests\Fixtures\inc\Engine\Preload;

use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * Wrapper class used to test the results.
 */
class PartialProcess_Wrapper extends PartialProcess {
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
