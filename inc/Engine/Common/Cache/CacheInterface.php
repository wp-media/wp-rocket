<?php

namespace WP_Rocket\Engine\Common\Cache;

interface CacheInterface extends \WP_Rocket\Dependencies\Psr\SimpleCache\CacheInterface {

	/**
	 * Generate the real URL.
	 *
	 * @param string $url original URL.
	 * @return string
	 */
	public function generate_url( string $url ): string;

	/**
	 * Is the root path available.
	 *
	 * @return bool
	 */
	public function is_accessible(): bool;

	/**
	 * Get root path from the cache.
	 *
	 * @return string
	 */
	public function get_root_path(): string;

	/**
	 * Generate a path from the URL.
	 *
	 * @param string $url URL to change to a path.
	 * @return string
	 */
	public function generate_path( string $url ): string;

	/**
	 * Wipes the whole cache directory.
	 *
	 * @param array $preserve_dirs List of directories to be preserved.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function full_clear( array $preserve_dirs = [] ): bool;
}
