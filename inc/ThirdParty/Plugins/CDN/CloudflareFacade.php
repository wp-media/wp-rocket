<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use CF\WordPress\Hooks;

class CloudflareFacade {
	/**
	 * Hooks class instance
	 *
	 * @var Hooks
	 */
	private $hooks;

	/**
	 * Instanciate the hooks class
	 *
	 * @return void
	 */
	private function set_hooks() {
		$this->hooks = new Hooks();
	}

	/**
	 * Calls purge everything from CF hooks class
	 *
	 * @return void
	 */
	public function purge_everything() {
		if ( is_null( $this->hooks ) ) {
			$this->set_hooks();
		}

		$this->hooks->purgeCacheEverything();
	}

	/**
	 * Calls purge relevant URLs from CF hooks class
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function purge_url( $post_id ) {
		if ( is_null( $this->hooks ) ) {
			$this->set_hooks();
		}

		$this->hooks->purgeCacheByRelevantURLs( $post_id );
	}
}
