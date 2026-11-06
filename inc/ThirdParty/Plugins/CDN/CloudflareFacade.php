<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

class CloudflareFacade {
	/**
	 * Hooks class instance
	 *
	 * @var null|object
	 */
	private $hooks = null;

	/**
	 * Instantiate the hooks class
	 *
	 * @return void
	 */
	private function set_hooks() {
		// Prioritize new namespace (Cloudflare plugin v4.13.0+).
		if ( class_exists( '\\Cloudflare\\APO\\WordPress\\Hooks' ) ) {
			$this->hooks = new \Cloudflare\APO\WordPress\Hooks();
			return;
		}

		// Fall back to old namespace (Cloudflare plugin < v4.14.0).
		if ( class_exists( '\\CF\\WordPress\\Hooks' ) ) {
			$this->hooks = new \CF\WordPress\Hooks();
		}

		// If neither class exists, hooks will remain null.
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

		if ( is_null( $this->hooks ) ) {
			return;
		}

		$this->hooks->purgeCacheEverything();
	}

	/**
	 * Calls purge relevant URLs from CF hooks class
	 *
	 * @param int|array $post_ids Post ID or array of post IDs.
	 *
	 * @return void
	 */
	public function purge_urls( $post_ids ) {
		if ( is_null( $this->hooks ) ) {
			$this->set_hooks();
		}

		if ( is_null( $this->hooks ) ) {
			return;
		}

		$this->hooks->purgeCacheByRelevantURLs( $post_ids );
	}
}
