<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

use WP_Rocket\Engine\Common\AbstractFileSystem;
use WP_Rocket\Logger\Logger;
use WP_Filesystem_Direct;

class Filesystem extends AbstractFileSystem {
	/**
	 * WP Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Path to the fonts storage
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Instantiate the class
	 *
	 * @param WP_Filesystem_Direct $filesystem WP Filesystem instance.
	 */
	public function __construct( $filesystem = null ) {
		parent::__construct( is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem );

		$this->path = rocket_get_constant( 'WP_ROCKET_CACHE_PATH', '' ) . 'fonts/' . get_current_blog_id() . '/';
	}

	/**
	 * Hash the url
	 *
	 * @param string $font_url Font url.
	 *
	 * @return string
	 */
	private function hash_url( string $font_url ): string {
		return md5( $font_url );
	}

	/**
	 * Write font css to path
	 *
	 * @param string $font_url The font url to save locally.
	 * @param string $provider The url of the page.
	 *
	 * @return bool
	 */
	public function write_font_css( string $font_url, string $provider ): bool {
		$font_provider_path = $this->get_font_provider_path( $provider );
		$hash_url           = $this->hash_url( $font_url );
		$file               = $this->get_fonts_full_path( $font_provider_path, $hash_url );
		$css_file_name      = $file . '.css';
		$relative_path      = $this->get_fonts_relative_path( $font_provider_path, $hash_url );

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		$start_time = microtime( true );

		$css_content = $this->download_font( html_entity_decode( $font_url ) );

		if ( ! $css_content ) {
			return false;
		}

		preg_match_all( '/url\((https:\/\/[^)]+)\)/', $css_content, $matches );
		$font_urls = $matches[1];
		$local_css = $css_content;

		foreach ( $font_urls as $font_url ) {
			$parsed_url = wp_parse_url( $font_url );
			$font_path  = $parsed_url['path'];
			$local_path = $file . $font_path;
			$local_dir  = dirname( $local_path );

			if ( ! rocket_mkdir_p( $local_dir ) ) {
				continue;
			}

			if ( ! $this->filesystem->exists( $local_path ) ) {
				$font_content = $this->download_font( $font_url );

				if ( ! $font_content ) {
					Logger::debug( 'Font download was not successful', [ 'Host Fonts Locally' ] );
					continue;
				}

				$this->write_file( $local_path, $font_content );
			}

			$local_url = content_url( $relative_path . $font_path );
			$local_css = str_replace( $font_url, $local_url, $local_css );
		}

		$end_time = microtime( true );
		$duration = $end_time - $start_time;

		// Add for test purpose.
		Logger::debug( "Font download and optimization duration in seconds -- $duration", [ 'Host Fonts Locally' ] );

		return $this->write_file( $css_file_name, $local_css );
	}

	/**
	 * Download font from external url
	 *
	 * @param string $url Url of the file to download.
	 *
	 * @return mixed
	 */
	private function download_font( string $url ) {
		$response = wp_safe_remote_get(
			$url,
			[
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				'httpversion' => '2.0',
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$content = wp_remote_retrieve_body( $response );

		if ( ! $content ) {
			return false;
		}

		return $content;
	}

	/**
	 * Get the fonts path for the css file.
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $hash               Url of the page.
	 *
	 * @return string Path for the font file.
	 */
	private function get_fonts_full_path( string $font_provider_path, string $hash ): string {
		return $this->path . $font_provider_path . $this->hash_to_path( $hash );
	}


	/**
	 * Get the fonts relative paths
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $hash               Hash of the font url.
	 *
	 * @return string
	 */
	private function get_fonts_relative_path( string $font_provider_path, string $hash ): string {
		$full_path      = $this->path . $font_provider_path;
		$wp_content_dir = rocket_get_constant( 'WP_CONTENT_DIR' );
		$relative_path  = str_replace( $wp_content_dir, '', $full_path );

		return $relative_path . $this->hash_to_path( $hash );
	}

	/**
	 * Get the fonts provider path
	 *
	 * @param string $provider The font provider.
	 *
	 * @return string
	 */
	private function get_font_provider_path( string $provider ): string {
		$provider = str_replace( '_', '-', $provider );

		return $provider . '/';
	}

	/**
	 * Deletes the locally stored fonts for the corresponding url
	 *
	 * @since 3.18
	 *
	 * @param string $url The url of the page to be deleted.
	 *
	 * @return bool
	 */
	public function delete_font_css( string $url ): bool {
		$dir = $this->get_fonts_full_path( $this->get_font_provider_path( $url ), $url );

		return $this->delete_file( $dir );
	}

	/**
	 * Converts hash to path with filtered number of levels
	 *
	 * @param string $hash Hash value.
	 *
	 * @return string
	 */
	private function hash_to_path( string $hash ): string {
		/**
		 * Filters the number of sub-folders level to create for used CSS storage
		 *
		 * @since 3.18
		 *
		 * @param int $levels Number of levels.
		 */
		$levels = wpm_apply_filters_typed( 'integer', 'rocket_media_font_dir_level', 3 );

		$base   = substr( $hash, 0, $levels );
		$remain = substr( $hash, $levels );

		$path_array   = str_split( $base );
		$path_array[] = $remain;

		return implode( '/', $path_array );
	}
}
