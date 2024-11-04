<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Controller;

class Filesystem {
	/**
	 * WP Filesystem instance
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Path to the fonts storage
	 *
	 * @var string
	 */
	private $path;

	/**
	 * Instantiate the class
	 *
	 * @param string               $base_path Base path to the fonts storage.
	 * @param WP_Filesystem_Direct $filesystem WP Filesystem instance.
	 */
	public function __construct( $base_path, $filesystem = null ) {
		$this->filesystem = is_null( $filesystem ) ? rocket_direct_filesystem() : $filesystem;
		$this->path       = $base_path . get_current_blog_id() . '/';
	}

	/**
	 * Write font css to path
	 *
	 * @param string $font_url The font url to save locally.
	 * @param string $provider The url of the page.
	 * @param int    $version  The version of the provider api.
	 *
	 * @return bool
	 */
	public function write_font_css( string $font_url, string $provider, int $version ): bool {
		global $wp;
		$url = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );

		$font_provider = $this->get_font_provider_path( $provider );
		$file          = $this->get_fonts_full_path( $font_provider, $url );
		$css_file_name = $file . md5( $url ) . '.css';
		$relative_path = $this->get_fonts_relative_path( $font_provider, $url );

		if ( ! rocket_mkdir_p( dirname( $file ) ) ) {
			return false;
		}

		$css_content = $this->download_font( html_entity_decode( $font_url ) );

		if ( ! $css_content ) {
			return false;
		}

		preg_match_all( '/url\((https:\/\/[^)]+)\)/', $css_content, $matches );
		$font_urls = $matches[1];
		$local_css = $css_content;

		foreach ( $font_urls as $font_url ) {
			$parsed_url = wp_parse_url( $font_url );
			$path_parts = explode( '/', trim( $parsed_url['path'], '/' ) );
			$local_path = $file . implode( '/', $path_parts );
			$local_dir  = dirname( $local_path );

			rocket_mkdir_p( $local_dir );

			if ( ! file_exists( $local_path ) ) {
				$font_content = $this->download_font( $font_url );

				if ( ! $font_content ) {
					continue;
				}

				$this->filesystem->put_contents( $local_path, $font_content, rocket_get_filesystem_perms( 'file' ) );
			}

			$local_url = content_url( $relative_path . implode( '/', $path_parts ) );
			$local_css = str_replace( $font_url, $local_url, $local_css );
		}

		return $this->filesystem->put_contents( $css_file_name, $local_css, rocket_get_filesystem_perms( 'file' ) );
	}

	/**
	 * Download font from external url
	 *
	 * @param string $url Url of the file to download.
	 *
	 * @return bool|string
	 */
	private function download_font( string $url ): bool|string {
		$content = wp_remote_retrieve_body(
			wp_remote_get(
			$url,
			[
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				'httpversion' => '2.0',
			]
			)
			);

		if ( ! $content ) {
			return false;
		}

		return $content;
	}

	/**
	 * Get the fonts path for the css file.
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $url Url of the page.
	 *
	 * @return string Path for the font file.
	 */
	private function get_fonts_full_path( string $font_provider_path, string $url ): string {
		return $this->path . $font_provider_path . md5( $url ) . '/';
	}


	/**
	 * Get the fonts relative paths
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $url Url of the page.
	 *
	 * @return string
	 */
	private function get_fonts_relative_path( string $font_provider_path, string $url ): string {
		$full_path     = $this->path . $font_provider_path;
		$relative_path = str_replace( WP_CONTENT_DIR, '', $full_path );

		return $relative_path . md5( $url ) . '/';
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
	 * @since 3.11.4
	 *
	 * @param string $url The url of the page to be deleted.
	 *
	 * @return bool
	 */
	public function delete_font_css( string $url ): bool {
		$dir = $this->get_fonts_full_path( $this->get_font_provider_path( $url ), $url );

		return $this->filesystem->delete( $dir, true, 'd' );
	}

	/**
	 * Deletes all the font CSS files
	 */
	public function delete_all_font_css() {
		// TODO:create method to recursively delete all locally stored fonts.
	}
}
