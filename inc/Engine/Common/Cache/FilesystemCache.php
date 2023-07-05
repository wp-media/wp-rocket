<?php

namespace WP_Rocket\Engine\Common\Cache;

use WP_Rocket\Dependencies\Psr\SimpleCache\CacheInterface;
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
	 */
	public function get( $key, $default = null ) {
		$path = $this->generate_path( $key );
		if ( ! $this->filesystem->exists( $path ) ) {
			return $default;
		}

		$content = $this->filesystem->get_contents( $path );

		return $content;
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
	 */
	public function set( $key, $value, $ttl = null ) {
		$path = $this->generate_path( $key );
		$directory = dirname( $path );
		rocket_mkdir_p( $directory );
		return $this->filesystem->put_contents( $path, $value );
	}

	/**
	 * Delete an item from the cache by its unique key.
	 *
	 * @param string $key The unique cache key of the item to delete.
	 *
	 * @return bool True if the item was successfully removed. False if there was an error.
	 *
	 * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
	 */
	public function delete( $key ) {
		$root_path  = _rocket_get_wp_rocket_cache_path() . $this->root_folder;
		$parsed_url = get_rocket_parse_url( $key );
		$path       = $root_path . $parsed_url['host'] . $parsed_url['path'];
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
		$root_path = _rocket_get_wp_rocket_cache_path() . $this->root_folder;
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable, or if any of the $keys are not a legal value.
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if $values is neither an array nor a Traversable, or if any of the $values are not a legal value.
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable, or if any of the $keys are not a legal value.
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
	 *
	 * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
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
		if ( ! $this->filesystem->exists( $path ) ) {
			return $url;
		}

		$wp_content_dir = rocket_get_constant( 'WP_CONTENT_DIR' );

		$wp_content_url = rocket_get_constant( 'WP_CONTENT_URL' );

		$relative_path = str_replace( $wp_content_dir, '', $path );

		return $wp_content_url . $relative_path;
	}

	/**
	 * Generate a path from the URL.
	 *
	 * @param string $url URL to change to a path.
	 * @return string
	 */
	protected function generate_path( string $url ):string {
		$root_path  = _rocket_get_wp_rocket_cache_path() . $this->root_folder;
		$parsed_url = get_rocket_parse_url( $url );
		return $root_path . $parsed_url['host'] . $parsed_url['path'];
	}
}
