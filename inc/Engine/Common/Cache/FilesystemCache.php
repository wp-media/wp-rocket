<?php

namespace WP_Rocket\Engine\Common\Cache;

use WP_Rocket\Dependencies\Psr\SimpleCache\InvalidArgumentException;
use WP_Filesystem_Direct;

class FilesystemCache implements CacheInterface {

	/**
	 * Root folder from the path.
	 *
	 * @var string
	 */
	protected $root_folder;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Class instantiation.
	 *
	 * @param string                    $root_folder Root folder from the path.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( string $root_folder, WP_Filesystem_Direct $filesystem = null ) {
		$this->root_folder = $root_folder;
		$this->filesystem  = $filesystem ?: rocket_direct_filesystem();
	}


	/**
	 * Fetches a value from the cache.
	 *
	 * @param string $key     The unique key of this item in the cache.
	 * @param mixed  $default Default value to return if the key does not exist.
	 *
	 * @return mixed The value of the item from the cache, or $default in case of cache miss.
	 */
	public function get( $key, $default = null ) {
		$path = $this->generate_path( $key );

		if ( ! $this->filesystem->exists( $path ) ) {
			return $default;
		}

		return $this->filesystem->get_contents( $path );
	}

	/**
	 * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
	 *
	 * @param string                 $key   The key of the item to store.
	 * @param mixed                  $value The value of the item to store, must be serializable.
	 * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
	 *                                      the driver supports TTL then the library may set a default value
	 *                                      for it or let the driver take care of that.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function set( $key, $value, $ttl = null ) {
		$path      = $this->generate_path( $key );
		$directory = dirname( $path );
		rocket_mkdir_p( $directory, $this->filesystem );
		return $this->filesystem->put_contents( $path, $value, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 */
	public function delete( $key ) {
		$path = $this->generate_path( $key );
		if ( ! $this->filesystem->exists( $path ) ) {
			return false;
		}
		if ( $this->filesystem->is_dir( $path ) ) {
			rocket_rrmdir( $path, [], $this->filesystem );
			return true;
		}

		return $this->filesystem->delete( $path );
	}

	/**
	 * Wipes clean the entire cache's keys.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function clear() {
		$root_path = $this->get_root_path();
		if ( ! $this->filesystem->exists( $root_path ) ) {
			return false;
		}
		rocket_rrmdir( $root_path, [], $this->filesystem );
		return true;
	}

	/**
	 * Obtains multiple cache items by their unique keys.
	 *
	 * @param iterable $keys    A list of keys that can obtained in a single operation.
	 * @param mixed    $default Default value to return for keys that do not exist.
	 *
	 * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
	 */
	public function getMultiple( $keys, $default = null ) {
		$results = [];
		foreach ( $keys as $key ) {
			$results[ $key ] = $this->get( $key, $default );
		}
		return $results;
	}

	/**
	 * Persists a set of key => value pairs in the cache, with an optional TTL.
	 *
	 * @param iterable               $values A list of key => value pairs for a multiple-set operation.
	 * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
	 *                                       the driver supports TTL then the library may set a default value
	 *                                       for it or let the driver take care of that.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function setMultiple( $values, $ttl = null ) {
		$result = true;
		foreach ( $values as $key => $value ) {
			$result &= $this->set( $key, $value, $ttl );
		}
		return (bool) $result;
	}

	/**
	 * Deletes multiple cache items in a single operation.
	 *
	 * @param iterable $keys A list of string-based keys to be deleted.
	 *
	 * @return bool True if the items were successfully removed. False if there was an error.
	 */
	public function deleteMultiple( $keys ) {
		$result = true;
		foreach ( $keys as $key ) {
			$result &= $this->delete( $key );
		}
		return (bool) $result;
	}

	/**
	 * Determines whether an item is present in the cache.
	 *
	 * NOTE: It is recommended that has() is only to be used for cache warming type purposes
	 * and not to be used within your live applications operations for get/set, as this method
	 * is subject to a race condition where your has() will return true and immediately after,
	 * another script can remove it making the state of your app out of date.
	 *
	 * @param string $key The cache item key.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		$path = $this->generate_path( $key );

		return $this->filesystem->exists( $path );
	}

	/**
	 * Generate the real URL.
	 *
	 * @param string $url original URL.
	 * @return string
	 */
	public function generate_url( string $url ): string {
		$path = $this->generate_path( $url );

		$wp_content_dir = rocket_get_constant( 'WP_CONTENT_DIR' );

		$wp_content_url = rocket_get_constant( 'WP_CONTENT_URL' );

		$relative_path = str_replace( $wp_content_dir, '', $path );

		$generated_url = $wp_content_url . $relative_path;

		return (string) apply_filters( 'rocket_css_url', $generated_url );
	}

	/**
	 * Generate a path from the URL.
	 *
	 * @param string $url URL to change to a path.
	 * @return string
	 */
	public function generate_path( string $url ): string {
		$root_path       = $this->get_root_path();
		$root_path       = rtrim( $root_path, '/' );
		$parsed_url      = get_rocket_parse_url( $url );
		$parsed_url_path = trim( $parsed_url['path'], '/' );

		$home_url        = home_url();
		$home_parsed_url = get_rocket_parse_url( $home_url );

		$host            = '' === $parsed_url['host'] || null === $parsed_url['host'] ? $home_parsed_url['host'] : $parsed_url['host'];
		$parsed_url_host = '/' . $host;

		return $root_path . $parsed_url_host . '/' . $parsed_url_path;
	}

	/**
	 * Is the root path available.
	 *
	 * @return bool
	 */
	public function is_accessible(): bool {
		$root_path = $this->get_root_path();
		if ( ! $this->filesystem->exists( $root_path ) ) {
			rocket_mkdir_p( $root_path, $this->filesystem );
		}

		return $this->filesystem->is_writable( $root_path );
	}

	/**
	 * Get root path from the cache.
	 *
	 * @return string
	 */
	public function get_root_path(): string {
		return rtrim( _rocket_normalize_path( rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) . $this->root_folder, '/' ) . '/';
	}
}
