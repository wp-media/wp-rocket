<?php
namespace WP_Rocket\CDN;

use WP_Rocket\Admin\Options_Data;

/**
 * CDN class
 *
 * @since 3.4
 * @author Remy Perona
 */
class CDN {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Search & Replace URLs with the CDN URLs in the provided content
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite( $html ) {
		$pattern = '#(?<url>(?<=[(\"\'])(?:(?:https?:|)' . preg_quote( $this->get_base_url(), '#' ) . ')?\/(?:(?:(?:' . $this->get_allowed_paths() . ')[^\"\')]+)|(?:[^\/\"\']+\.[^\/\"\')]+))(?=[\"\')]))#i';

		return preg_replace_callback(
			$pattern,
			function( $matches ) {
				return $this->rewrite_url( $matches['url'] );
			},
			$html
		);
	}

	/**
	 * Rewrites an URL with the CDN URL
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $url Original URL.
	 * @return string
	 */
	public function rewrite_url( $url ) {
		if ( ! $this->options->get( 'cdn' ) ) {
			return $url;
		}

		if ( $this->is_excluded( $url ) ) {
			return $url;
		}

		$cdn_urls = $this->get_cdn_urls( $this->get_zones_for_url( $url ) );

		if ( ! $cdn_urls ) {
			return $url;
		}

		$parsed_url = wp_parse_url( $url );
		$cdn_url    = untrailingslashit( $cdn_urls[ ( abs( crc32( $parsed_url['path'] ) ) % count( $cdn_urls ) ) ] );

		if ( ! isset( $parsed_url['host'] ) ) {
			return rocket_add_url_protocol( $cdn_url . '/' . ltrim( $url, '/' ) );
		}

		$home      = get_option( 'home' );
		$home_host = wp_parse_url( $home, PHP_URL_HOST );

		return str_replace( $home_host, $cdn_url, $url );
	}

	/**
	 * Rewrites URLs to CDN URLs in CSS content
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $content CSS content.
	 * @return string
	 */
	public function rewrite_css_properties( $content ) {
		if ( ! preg_match_all( '#url\(\s*(\'|")?\s*(?![\'"]?data)(?<url>(?:https?:|)' . preg_quote( $this->get_base_url(), '#' ) . '\/[^"|\'|\)|\s]+)\s*#i', $content, $matches, PREG_SET_ORDER ) ) {
			return $content;
		}

		foreach ( $matches as $property ) {
			/**
			 * Filters the URL of the CSS property
			 *
			 * @since 2.8
			 *
			 * @param string $url URL of the CSS property.
			 */
			$cdn_url     = $this->rewrite_url( apply_filters( 'rocket_cdn_css_properties_url', $property['url'] ) );
			$replacement = str_replace( $property['url'], $cdn_url, $property[0] );
			$content     = str_replace( $property[0], $replacement, $content );
		}

		return $content;
	}

	/**
	 * Get all CDN URLs for one or more zones.
	 *
	 * @since 2.1
	 * @since 3.0 Don't check for WP Rocket CDN option activated to be able to use the function on Hosting with CDN auto-enabled.
	 *
	 * @param  string $zones List of zones. Default is 'all'.
	 * @return array
	 */
	public function get_cdn_urls( $zones = 'all' ) {
		$hosts    = [];
		$cdn_urls = $this->options->get( 'cdn_cnames', [] );

		if ( $cdn_urls ) {
			$cdn_zones = $this->options->get( 'cdn_zone', [] );
			$zones     = (array) $zones;

			foreach ( $cdn_urls as $k => $urls ) {
				if ( ! in_array( $cdn_zones[ $k ], $zones, true ) ) {
					continue;
				}

				$urls = explode( ',', $urls );
				$urls = array_map( 'trim', $urls );

				foreach ( $urls as $url ) {
					$hosts[] = $url;
				}
			}
		}

		/**
		 * Filter all CDN URLs.
		 *
		 * @since 2.7
		 *
		 * @param array $hosts List of CDN URLs.
		 */
		$hosts = (array) apply_filters( 'rocket_cdn_cnames', $hosts );
		$hosts = array_filter( $hosts );
		$hosts = array_flip( array_flip( $hosts ) );
		$hosts = array_values( $hosts );

		return $hosts;
	}

	/**
	 * Gets the base URL for the website
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return string
	 */
	private function get_base_url() {
		$home = get_option( 'home' );

		return substr( $home, strpos( $home, '//' ) );
	}

	/**
	 * Gets the allowed paths as a regex pattern for the CDN rewrite
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return string
	 */
	private function get_allowed_paths() {
		$wp_content_dirname = ltrim( wp_parse_url( content_url(), PHP_URL_PATH ), '/' );

		$upload_dirname = '';
		$uploads_info   = wp_upload_dir();

		if ( ! empty( $uploads_info['baseurl'] ) ) {
			$upload_dirname = '|' . ltrim( trailingslashit( wp_parse_url( $uploads_info['baseurl'], PHP_URL_PATH ) ), '/' );
		}

		return $wp_content_dirname . $upload_dirname . '|wp-includes';
	}

	/**
	 * Checks if the provided URL can be rewritten with the CDN URL
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $url URL to check.
	 * @return boolean
	 */
	private function is_excluded( $url ) {
		if ( 'php' === pathinfo( strtok( $url, '?' ), PATHINFO_EXTENSION ) ) {
			return true;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! isset( $path ) ) {
			return true;
		}

		if ( '/' === $path ) {
			return true;
		}

		if ( preg_match( '#^(' . $this->get_excluded_files() . ')$#', $path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets the CDN zones for the provided URL
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $url URL to check.
	 * @return array
	 */
	private function get_zones_for_url( $url ) {
		$zones = [ 'all' ];

		$ext = pathinfo( $url, PATHINFO_EXTENSION );

		$image_types = [
			'jpg',
			'jpeg',
			'jpe',
			'png',
			'gif',
			'webp',
			'bmp',
			'tiff',
		];

		if ( 'css' === $ext || 'js' === $ext ) {
			$zones[] = 'css_and_js';
		}

		if ( 'css' === $ext ) {
			$zones[] = 'css';
		}

		if ( 'js' === $ext ) {
			$zones[] = 'js';
		}

		if ( in_array( $ext, $image_types, true ) ) {
			$zones[] = 'images';
		}

		return $zones;
	}

	/**
	 * Get all files we don't allow to get in CDN.
	 *
	 * @since 2.5
	 *
	 * @return string A pipe-separated list of excluded files.
	 */
	private function get_excluded_files() {
		$files = $this->options->get( 'cdn_reject_files', [] );

		/**
			* Filter the excluded files.
			*
			* @since 2.5
			*
			* @param array $files List of excluded files.
		*/
		$files = (array) apply_filters( 'rocket_cdn_reject_files', $files );
		$files = array_filter( $files );
		$files = array_flip( array_flip( $files ) );

		return implode( '|', $files );
	}
}
