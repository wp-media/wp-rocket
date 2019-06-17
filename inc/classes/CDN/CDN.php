<?php
namespace WP_Rocket\CDN;

class CDN {
	private $options;

	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	public function rewrite( $html ) {
		$pattern = '#(?<=[(\"\'])(?:(?:https?:|)' . $this->get_base_url() . ')?\/(?:(?:(?:' . $this->get_allowed_paths() . ')[^\"\')]+)|(?:[^\/\"\']+\.[^\/\"\')]+))(?=[\"\')])#i';

		return preg_replace_callback( $pattern, [ $this, 'rewrite_url' ], $html );
	}

	public function rewrite_url( $url ) {
		if ( $this->is_excluded( $url ) ) {
			return $url;
		}

	}

	private function get_base_url() {
		$home = get_option( 'home' );

		return preg_quote( substr( $home, strpos( $home, '//' ) ), '#' );
	}

	private function get_allowed_paths() {
		$wp_content_dirname = wp_parse_url( content_url(), PHP_URL_PATH );

		$custom_upload_dirname = '';
		$uploads_info          = wp_upload_dir();

		if ( ! empty( $uploads_info['baseurl'] ) ) {
			$custom_upload_dirname = '|' . trailingslashit( wp_parse_url( $uploads_info['baseurl'], PHP_URL_PATH ) );
		}

		return preg_quote( $wp_content_dirname . $custom_upload_dirname . '|wp-includes', '#' );
	}

	private function is_excluded( $url ) {
		if ( false !== stripos( $url, '.php' ) ) {
			return true;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );

		if ( ! isset( $path ) ) {
			return true;
		}

		if ( preg_match( '#(' . $this->get_excluded_files() . ')#', $path ) ) {
			return true;
		}

		return false;
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

	/**
	 * Get all CDN URLs for one or more zones.
	 *
	 * @since 2.1
	 * @since 3.0 Don't check for WP Rocket CDN option activated to be able to use the function on Hosting with CDN auto-enabled.
	 *
	 * @param  string $zones List of zones. Default is 'all'.
	 * @return array
	 */
	private function get_cdn_urls( $zones = 'all' ) {
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
}
