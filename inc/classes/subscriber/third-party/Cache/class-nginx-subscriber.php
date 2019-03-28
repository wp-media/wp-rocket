<?php
namespace WP_Rocket\Subscriber\Third_Party\Cache;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

/**
 * Subscriber to sync NGINX cache with WP Rocket cache
 *
 * @since 3.3
 * @author Remy Perona
 */
class NGINX_Subscriber implements Subscriber_Interface {
	/**
	 * Options instance
	 *
	 * @var Options_data
	 */
	private $options;

	/**
	 * NGINX cache path
	 *
	 * @var string
	 */
	private $cache_path;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;

		/**
		 * Filters the default value for the NGINX cache path
		 *
		 * @since 3.3
		 * @author Remy Perona
		 *
		 * @param string $cache_path Absolute path to NGINX cache folder.
		 */
		$this->cache_path = apply_filters( 'rocket_nginx_cache_path', '/var/run/nginx-cache' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'before_rocket_clean_domain' => [ 'clean_domain', 10, 3 ],
			'before_rocket_clean_file'   => 'clean_file',
			'before_rocket_clean_home'   => [ 'clean_home', 10, 2 ],
		];
	}

	/**
	 * Purge all the domain
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 * @param string $url  The home url.
	 * @return void
	 */
	public function clean_domain( $root, $lang, $url ) {
		if ( ! $this->options->get( 'nginx_auto_purge' ) ) {
			return;
		}

		$this->purge_cache( $this->cache_path );
	}

	/**
	 * Purge a specific page
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $url The url to purge.
	 */
	public function clean_file( $url ) {
		if ( ! $this->options->get( 'nginx_auto_purge' ) ) {
			return;
		}

		$url = str_replace( '*', '', $url );
		$this->send_purge_request( $url );
	}

	/**
	 * Purge the homepage and its pagination
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $root The path of home cache file.
	 * @param string $lang The current lang to purge.
	 */
	public function clean_home( $root, $lang ) {
		if ( ! $this->options->get( 'nginx_auto_purge' ) ) {
			return;
		}

		$url = trailingslashit( get_rocket_i18n_home_url( $lang ) );

		$this->send_purge_request( $url );
	}

	/**
	 * Sends the purge request to NGINX Cache
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $url URL to purge.
	 * @return void
	 */
	private function send_purge_request( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! isset( $parsed_url['path'] ) ) {
			$parsed_url['path'] = '';
		}

		$purge_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/purge' . $parsed_url['path'];

		if ( isset( $parsed_url['query'] ) && '' !== $parsed_url['query'] ) {
			$purge_url .= '?' . $parsed_url['query'];
		}

		$purge_request = wp_remote_get( $purge_url );

		if ( is_wp_error( $purge_request ) ) {
			Logger::error( 'Error while purging NGINX Cache for url: ' . $purge_url, [ 'NGINX Add-on' ] );
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $purge_request );

		if ( 200 === $response_code ) {
			Logger::info( 'NGINX Cache purged for url: ' . $purge_url, [ 'NGINX Add-on' ] );
			return;
		}

		if ( 404 === $response_code ) {
			Logger::error( 'URL currently not NGINX cached: ' . $purge_url, [ 'NGINX Add-on'] );
			return;
		}
	}

	/**
	 * Purge NGINX cache folder
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @param string $dir Absolute NGINX cache folder path.
	 * @return void
	 */
	private function purge_cache( $dir ) {
		if ( ! \rocket_direct_filesystem()->exists( $dir ) ) {
			return;
		}

		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(
				$dir,
				\RecursiveDirectoryIterator::SKIP_DOTS
			),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $files as $file ) {
			\rocket_direct_filesystem()->delete( $file->getRealPath(), true );
		}

		Logger::info( 'NGINX Cache fully purged', [ 'NGINX Add-on' ] );
	}
}
