<?php
namespace WP_Rocket\Engine\Cache\PurgeExpired;

use WP_Rocket\Buffer\Cache;

/**
 * Purge expired cache files based on the defined lifespan
 *
 * @since  3.4
 */
class PurgeExpiredCache {
	/**
	 * Path to the global cache folder.
	 *
	 * @since  3.4
	 *
	 * @var string
	 */
	private $cache_path;

	/**
	 * Filesystem object.
	 *
	 * @since  3.4
	 *
	 * @var null|\WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Constructor
	 *
	 * @param string $cache_path Path to the global cache folder.
	 */
	public function __construct( $cache_path ) {
		$this->cache_path = $cache_path;
	}

	/**
	 * Perform the event action.
	 *
	 * @since  3.4
	 *
	 * @param int $lifespan The cache lifespan in seconds.
	 */
	public function purge_expired_files( $lifespan ) {
		if ( ! $lifespan ) {
			// Uh?
			return;
		}

		$urls           = get_rocket_i18n_uri();
		$file_age_limit = time() - $lifespan;

		/**
		 * Filter home URLs that will be searched for old cache files.
		 *
		 * @since  3.4
		 *
		 * @param array $urls           URLs that will be searched for old cache files.
		 * @param int   $file_age_limit Timestamp of the maximum age files must have.
		 */
		$urls = wpm_apply_filters_typed( 'array', 'rocket_automatic_cache_purge_urls', $urls, $file_age_limit );

		if ( ! is_array( $urls ) ) {
			// I saw what you did ಠ_ಠ.
			$urls = get_rocket_i18n_uri();
		}

		$urls = array_filter( $urls, 'is_string' );
		$urls = array_filter( $urls );

		if ( ! $urls ) {
			return;
		}

		$urls = array_unique( $urls );

		if ( empty( $this->filesystem ) ) {
			$this->filesystem = rocket_direct_filesystem();
		}

		$deleted       = [];
		$cache_enabled = Cache::can_generate_caching_files();

		foreach ( $urls as $url ) {
			/**
			 * Fires before purging a cache directory.
			 *
			 * @since  3.4
			 *
			 * @param string $url          The home url.
			 * @param int    $file_age_limit Timestamp of the maximum age files must have.
			 */
			do_action( 'rocket_before_automatic_cache_purge_dir', $url, $file_age_limit );

			$url_deleted = [];

			if ( $cache_enabled ) {
				// Get the directory names.
				$file = get_rocket_parse_url( $url );

				/** This filter is documented in inc/front/htaccess.php */
				if ( apply_filters( 'rocket_url_no_dots', false ) ) {
					$file['host'] = str_replace( '.', '_', $file['host'] );
				}

				$sub_dir = rtrim( $file['path'], '/' );
				$files   = $this->get_cache_files_in_dir( $file );

				foreach ( $files as $item ) {
					$dir_path     = $item->getPathname();
					$sub_dir_path = $dir_path . $sub_dir;

					// Time to cut old leaves.
					$item_paths = $this->purge_dir( $sub_dir_path, $file_age_limit );

					if ( $item_paths ) {
						$url_deleted[] = [
							'home_url'  => $url,
							'home_path' => $sub_dir_path,
							'logged_in' => $dir_path !== $this->cache_path . $file['host'],
							'files'     => $item_paths,
						];
					}

					if ( $this->is_dir_empty( $dir_path ) ) {
						// If the folder is empty, remove it.
						$this->filesystem->delete( $dir_path );
					}
				}

				if ( $url_deleted ) {
					$deleted = array_merge( $deleted, $url_deleted );
				}
			}

			$args = [
				'url'            => $url,
				'lifespan'       => $lifespan,
				'file_age_limit' => $file_age_limit,
			];

			/**
			 * Fires after a cache directory is purged.
			 *
			 * @since  3.4
			 *
			 * @param array $deleted {
			 *     An array of arrays sharing the same home URL, described like: {
			 *         @type string $home_url  The home URL. This is the same as $args['url'].
			 *         @type string $home_path Path to home.
			 *         @type bool   $logged_in True if the home path corresponds to a logged in user’s folder.
			 *         @type array  $files     A list of paths of files that have been deleted.
			 *     }
			 *     Ex:
			 *     [
			 *         [
			 *             'home_url'  => 'http://example.com/home1',
			 *             'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1',
			 *             'logged_in' => false,
			 *             'files'     => [
			 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1/deleted-page',
			 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1/very-dead-page',
			 *             ],
			 *         ],
			 *         [
			 *             'home_url'  => 'http://example.com/home1',
			 *             'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
			 *             'logged_in' => true,
			 *             'files'     => [
			 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/how-to-prank-your-coworkers',
			 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/best-source-of-gifs',
			 *             ],
			 *         ],
			 *     ]
			 * @param array $args    {
			 *     @type string $url            The home url.
			 *     @type int    $lifespan       Files lifespan in seconds.
			 *     @type int    $file_age_limit Timestamp of the maximum age files must have. This is basically `time() - $lifespan`.
			 * }
			 */
			do_action( 'rocket_after_automatic_cache_purge_dir', $url_deleted, $args );
		}

		$args = [
			'lifespan'       => $lifespan,
			'file_age_limit' => $file_age_limit,
		];

		/**
		 * Fires after cache directories are purged.
		 *
		 * @since  3.4
		 *
		 * @param array $deleted {
		 *     An array of arrays, described like: {
		 *         @type string $home_url  The home URL.
		 *         @type string $home_path Path to home.
		 *         @type bool   $logged_in True if the home path corresponds to a logged in user’s folder.
		 *         @type array  $files     A list of paths of files that have been deleted.
		 *     }
		 *     Ex:
		 *     [
		 *         [
		 *             'home_url'  => 'http://example.com/home1',
		 *             'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1',
		 *             'logged_in' => false,
		 *             'files'     => [
		 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1/deleted-page',
		 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com/home1/very-dead-page',
		 *             ],
		 *         ],
		 *         [
		 *             'home_url'  => 'http://example.com/home1',
		 *             'home_path' => '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1',
		 *             'logged_in' => true,
		 *             'files'     => [
		 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/how-to-prank-your-coworkers',
		 *                 '/path-to/home1/wp-content/cache/wp-rocket/example.com-Greg-594d03f6ae698691165999/home1/best-source-of-gifs',
		 *             ],
		 *         ],
		 *         [
		 *             'home_url'  => 'http://example.com/home4',
		 *             'home_path' => '/path-to/home4/wp-content/cache/wp-rocket/example.com-Greg-71edg8d6af865569979569/home4',
		 *             'logged_in' => true,
		 *             'files'     => [
		 *                 '/path-to/home4/wp-content/cache/wp-rocket/example.com-Greg-71edg8d6af865569979569/home4/easter-eggs-in-code-your-best-opportunities',
		 *             ],
		 *         ],
		 *     ]
		 * }
		 * @param array $args    {
		 *     @type int $lifespan       Files lifespan in seconds.
		 *     @type int $file_age_limit Timestamp of the maximum age files must have. This is basically `time() - $lifespan`.
		 * }
		 */
		do_action( 'rocket_after_automatic_cache_purge', $deleted, $args );
	}


	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */
	/**
	 * Get all cache files for the provided URL
	 *
	 * @since 3.4
	 *
	 * @param array $file An array of the parsed URL parts.
	 * @return array|\CallbackFilterIterator
	 */
	private function get_cache_files_in_dir( $file ) {
		// Grab cache folders.
		$host_pattern = '@^' . preg_quote( $file['host'], '@' ) . '@';
		$sub_dir      = rtrim( $file['path'], '/' );

		try {
			$iterator = new \DirectoryIterator( $this->cache_path );
		}
		catch ( \Exception $e ) {
			return [];
		}

		return new \CallbackFilterIterator(
			$iterator,
			function ( $current ) use ( $host_pattern, $sub_dir ) {

				if ( ! $current->isDir() || $current->isDot() ) {
					// We look for folders only, and don't want '.' nor '..'.
					return false;
				}

				if ( ! preg_match( $host_pattern, $current->getFilename() ) ) {
					// Not the right host.
					return false;
				}

				if ( '' !== $sub_dir && ! $this->filesystem->exists( $current->getPathname() . $sub_dir ) ) {
					// Not the right path.
					return false;
				}

				return true;
			}
		);
	}

	/**
	 * Purge a folder from old files.
	 *
	 * @since  3.4
	 *
	 * @param  string $dir_path     Path to the folder to purge.
	 * @param  int    $file_age_limit Timestamp of the maximum age files must have.
	 * @return array                A list of files that have been deleted.
	 */
	private function purge_dir( $dir_path, $file_age_limit ) {
		$deleted = [];

		try {
			$iterator = new \DirectoryIterator( $dir_path );
		}
		catch ( \Exception $e ) {
			return [];
		}

		foreach ( $iterator as $item ) {
			if ( $item->isDot() ) {
				continue;
			}

			if ( $item->isDir() ) {
				/**
				 * A folder, let’s see what’s in there.
				 * Maybe there’s a dinosaur fossil or a hidden treasure.
				 */
				$dir_deleted = $this->purge_dir( $item->getPathname(), $file_age_limit );
				$deleted     = array_merge( $deleted, $dir_deleted );

			} elseif ( $item->isFile() && $item->getMTime() < $file_age_limit ) {
				$file_path = $item->getPathname();

				/**
				 * The file is older than our limit.
				 * This will also delete the file if `$item->getMTime()` fails.
				 */
				if ( ! $this->filesystem->delete( $file_path ) ) {
					continue;
				}

				/**
				 * A page can have multiple cache files:
				 * index(-mobile)(-https)(-dynamic-cookie-key){0,*}.html(_gzip).
				 */
				$dir_path = dirname( $file_path );

				if ( ! in_array( $dir_path, $deleted, true ) ) {
					$deleted[] = $dir_path;
				}
			}
		}

		if ( $this->is_dir_empty( $dir_path ) ) {
			// If the folder is empty, remove it.
			$this->filesystem->delete( $dir_path );
		}

		return $deleted;
	}

	/**
	 * Tell if a folder is empty.
	 *
	 * @since  3.4
	 *
	 * @param  string $dir_path Path to the folder to purge.
	 * @return bool             True if empty. False if it contains files.
	 */
	private function is_dir_empty( $dir_path ) {
		try {
			$iterator = new \DirectoryIterator( $dir_path );
		}
		catch ( \Exception $e ) {
			return true;
		}

		foreach ( $iterator as $item ) {
			if ( $item->isDot() ) {
				continue;
			}
			return false;
		}

		return true;
	}

	/**
	 * Update lifespan option to convert old minutes to hours.
	 *
	 * @since 3.8
	 *
	 * @param int    $old_lifespan      Old value in minutes.
	 * @param string $old_lifespan_unit Old value of unit.
	 *
	 * @return void
	 */
	public function update_lifespan_value( $old_lifespan, $old_lifespan_unit ) {
		if ( 'MINUTE_IN_SECONDS' !== $old_lifespan_unit ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		if ( $old_lifespan > 0 && $old_lifespan < 60 ) {
			$old_lifespan = 60;
		}

		$options['purge_cron_unit']     = 'HOUR_IN_SECONDS';
		$options['purge_cron_interval'] = round( $old_lifespan / 60 );

		update_option( 'wp_rocket_settings', $options );
	}
}
