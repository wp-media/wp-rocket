<?php

namespace WP_Rocket\Engine\Common\Cache;

use WP_Rocket\Dependencies\Psr\SimpleCache\InvalidArgumentException;
use WP_Filesystem_Direct;

class FilesystemCache implements CacheInterface {

	/**
	 * File names that can be cached: stylesheets and their mapping files.
	 */
	private const ALLOWED_FILE_PATTERN = '/\.css(?:\.json)?\z/i';

	/**
	 * Inner extensions a web server may run as scripts. The PHP family only needs to not be followed by a letter, so ".php5", ".php;" or ".php_" match but ".phpbb" or ".pharmacy" don't.
	 */
	private const EXECUTABLE_EXTENSION_PATTERN = '/\.ph(?:p\d*|t|tml|ar|ps)(?![a-z])|\.(?:shtml?|f?cgi|pl|plx|ppl|perl|py|aspx?|jsp)(?:\.|\z)/i';

	/**
	 * Names starting with a dot, or ending with a dot or a space (trimmed by some filesystems).
	 */
	private const UNSAFE_NAME_PATTERN = '/^\.|[. ]\z/';

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
	public function __construct( string $root_folder, ?WP_Filesystem_Direct $filesystem = null ) {
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
	public function get( string $key, $default = null ) {
		$path = $this->generate_path( $key );

		if ( '' === $path || ! $this->filesystem->exists( $path ) ) {
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
	public function set( string $key, $value, $ttl = null ): bool {
		$path = $this->generate_path( $key );

		if ( '' === $path ) {
			return false;
		}

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
	public function delete( string $key ): bool {
		$path = $this->generate_path( $key );
		if ( '' === $path || ! $this->filesystem->exists( $path ) ) {
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
	public function clear(): bool {
		$root_path = $this->get_root_path();
		if ( ! $this->filesystem->exists( $root_path ) ) {
			return false;
		}
		rocket_rrmdir( $root_path, [], $this->filesystem );
		return true;
	}

	/**
	 * Clear the whole background-css directory.
	 *
	 * @param array $preserve_dirs List of directories to be preserved.
	 *
	 * @return bool True on success and false on failure.
	 */
	public function full_clear( array $preserve_dirs = [] ): bool {
		$base_path = $this->get_base_path();
		if ( ! $this->filesystem->exists( $base_path ) ) {
			return false;
		}

		if ( ! empty( $preserve_dirs ) ) {
			$preserve_dirs = array_map(
					function ( $dir ) use ( $base_path ) {
						return $base_path . $dir;
					},
				$preserve_dirs
				);
		}

		rocket_rrmdir( $base_path, $preserve_dirs, $this->filesystem );
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
	public function getMultiple( iterable $keys, $default = null ): iterable {
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
	public function setMultiple( iterable $values, $ttl = null ): bool {
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
	public function deleteMultiple( iterable $keys ): bool {
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
	public function has( string $key ): bool {
		$path = $this->generate_path( $key );

		return '' !== $path && $this->filesystem->exists( $path );
	}

	/**
	 * Generate the real URL.
	 *
	 * @param string $url original URL.
	 * @return string Cached file URL, or an empty string when the URL can't be mapped.
	 */
	public function generate_url( string $url ): string {
		$path = $this->generate_path( $url );

		if ( '' === $path ) {
			return '';
		}

		$wp_content_dir = rocket_get_constant( 'WP_CONTENT_DIR' );

		$wp_content_url = rocket_get_constant( 'WP_CONTENT_URL' );

		$relative_path = str_replace( $wp_content_dir, '', $path );

		$generated_url = $wp_content_url . $relative_path;

		return (string) apply_filters( 'rocket_css_url', $generated_url );
	}

	/**
	 * Generate a path from the URL.
	 *
	 * Dot segments are resolved and the result is kept inside the host directory of the cache root.
	 *
	 * @param string $url URL to change to a path.
	 * @return string Absolute file path, or an empty string when the URL can't be mapped to an allowed file inside the cache root.
	 */
	public function generate_path( string $url ): string {
		$root_path  = rtrim( $this->get_root_path(), '/' );
		$parsed_url = (array) get_rocket_parse_url( $url );
		$host       = (string) ( $parsed_url['host'] ?? '' );

		if ( '' === $host ) {
			$home_parsed_url = (array) get_rocket_parse_url( home_url() );
			$host            = (string) ( $home_parsed_url['host'] ?? '' );
		}

		if ( ! $this->is_valid_host( $host ) ) {
			return '';
		}

		$path = str_replace( '\\', '/', (string) ( $parsed_url['path'] ?? '' ) );

		// Control characters break file functions, the others are Windows-reserved (stream separator, wildcards).
		if ( preg_match( '/[\x00-\x1f:<>"|?*]/', $path ) ) {
			return '';
		}

		// The leading slash roots the normalization, so parent segments clamp at the host directory.
		$path = ltrim( rocket_realpath( '/' . $path ), '/' );

		if ( ! $this->is_valid_path( $path ) ) {
			return '';
		}

		$file_path = $root_path . '/' . $host . '/' . $path;

		if ( ! $this->is_inside_root( $file_path, $root_path . '/' . $host ) ) {
			return '';
		}

		return $file_path;
	}

	/**
	 * Checks the host can be used as a single directory name.
	 *
	 * @param string $host URL host.
	 * @return bool
	 */
	private function is_valid_host( string $host ): bool {
		return '' !== $host
			&& ! preg_match( '#[/\\\\:<>"|?*\x00-\x1f]|\.\.#', $host )
			&& ! preg_match( self::UNSAFE_NAME_PATTERN, $host );
	}

	/**
	 * Checks a normalized relative path points to an allowed file name.
	 *
	 * @param string $path Normalized path, without leading slash.
	 * @return bool
	 */
	private function is_valid_path( string $path ): bool {
		$segments = explode( '/', $path );
		$file     = (string) end( $segments );

		if ( ! preg_match( self::ALLOWED_FILE_PATTERN, $file ) || preg_match( self::EXECUTABLE_EXTENSION_PATTERN, $file ) ) {
			return false;
		}

		foreach ( $segments as $segment ) {
			if ( preg_match( self::UNSAFE_NAME_PATTERN, $segment ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks the path resolves inside the root path.
	 *
	 * @param string $path      Path to check.
	 * @param string $root_path Root path the path must stay in.
	 * @return bool
	 */
	private function is_inside_root( string $path, string $root_path ): bool {
		$root = _rocket_normalize_path( rocket_realpath( $root_path ) . '/' );

		return 0 === strpos( _rocket_normalize_path( rocket_realpath( $path ) ), $root );
	}

	/**
	 * Is the root path available.
	 *
	 * @return bool
	 */
	public function is_accessible(): bool {
		$base_path = $this->get_base_path();
		if ( ! $this->filesystem->exists( $base_path ) ) {
			rocket_mkdir_p( $base_path, $this->filesystem );
		}
		$root_path = $this->get_root_path();
		if ( ! $this->filesystem->exists( $root_path ) ) {
			rocket_mkdir_p( $root_path, $this->filesystem );
		}

		return $this->filesystem->is_writable( $root_path );
	}

	/**
	 * Get root path from the cache (including current blog ID) with trailing slash.
	 *
	 * @return string
	 */
	public function get_root_path(): string {
		return $this->get_base_path() . get_current_blog_id() . '/';
	}

	/**
	 * Get base path from the cache (/cache/background-css/) with trailing slash.
	 *
	 * @return string
	 */
	private function get_base_path(): string {
		return rtrim( _rocket_normalize_path( rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH' ) ) . $this->root_folder, '/' ) . '/';
	}
}
