<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts;

use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\AbstractFileSystem;
use WP_Rocket\Logger\Logger;

class Filesystem extends AbstractFileSystem {
	/**
	 * Characters allowed in a remote font path.
	 */
	private const FONT_PATH_CHARS = '#^[A-Za-z0-9._/-]+\z#';

	/**
	 * Normalized local font path: dot-free directory names, an optional numeric slice index and a font extension.
	 */
	private const LOCAL_FONT_PATH = '#^(?:/[a-z0-9_-]+)*/[a-z0-9_-]+(?:\.[0-9]+)?\.(?:woff2|woff|ttf|otf|eot)\z#i';

	/**
	 * Leading bytes of WOFF2, WOFF, TrueType, OpenType and font collection files.
	 */
	private const FONT_SIGNATURES = [ 'wOF2', 'wOFF', "\x00\x01\x00\x00", 'true', 'OTTO', 'ttcf' ];

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

		$this->path = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH', '' ) . 'fonts/' . get_current_blog_id() . '/';
	}

	/**
	 * Hashes the url
	 *
	 * @param string $url URL to get the hash from.
	 *
	 * @return string
	 */
	private function hash_url( string $url ): string {
		return md5( $url );
	}

	/**
	 * Checks if the file exists
	 *
	 * @param string $file Absolute path to the file.
	 *
	 * @return bool
	 */
	public function exists( string $file ): bool {
		return $this->filesystem->exists( $file );
	}

	/**
	 * Writes CSS & fonts locally
	 *
	 * @param string $css_url The CSS url to save locally.
	 * @param string $provider The font provider.
	 *
	 * @return bool
	 */
	public function write_font_css( string $css_url, string $provider ): bool {
		$font_provider_path = $this->get_font_provider_path( $provider );
		$css_filepath       = $this->get_absolute_path( $font_provider_path, 'css/' . $this->hash_to_path( $this->hash_url( $css_url ) ) . '.css' );
		$fonts_basepath     = $this->get_absolute_path( $font_provider_path, 'fonts' );

		if ( ! rocket_mkdir_p( dirname( $css_filepath ) ) ) {
			return false;
		}

		$start_time = microtime( true );

		$css_content = $this->get_remote_content( html_entity_decode( $css_url ) );

		if ( ! $css_content ) {
			return false;
		}

		preg_match_all( '/url\((https:\/\/[^)]+)\)/i', $css_content, $matches );
		$font_urls = $matches[1];
		$local_css = $css_content;

		$count_fonts      = 0;
		$download_average = 0;

		foreach ( $font_urls as $font_url ) {
			$font_path = $this->get_local_font_path( $font_url, $fonts_basepath );

			if ( '' === $font_path ) {
				Logger::debug( "Font $font_url kept remote", [ 'Host Fonts Locally' ] );
				continue;
			}

			$local_path = $fonts_basepath . $font_path;

			if ( ! $this->filesystem->exists( $local_path ) ) {
				$download_start = microtime( true );

				$font_content = $this->get_remote_content( $font_url );

				if ( ! $font_content ) {
					Logger::debug( 'Font download was not successful', [ 'Host Fonts Locally' ] );
					continue;
				}

				if ( ! $this->is_font_content( $font_content, $font_path ) ) {
					Logger::debug( "Font $font_url kept remote, its content is not a font file", [ 'Host Fonts Locally' ] );
					continue;
				}

				if ( ! rocket_mkdir_p( dirname( $local_path ) ) ) {
					continue;
				}

				$this->write_file( $local_path, $font_content );

				$download_end  = microtime( true );
				$download_time = $download_end - $download_start;

				$download_average += $download_time;

				++$count_fonts;

				Logger::debug( "Font $font_url download duration -- $download_time", [ 'Host Fonts Locally' ] );
			}

			$local_url = content_url( $this->get_fonts_relative_path( $font_provider_path, $font_path ) );
			$local_css = str_replace( $font_url, $local_url, $local_css );
		}

		// This filter is documented in inc/Engine/Optimization/CSSTrait.php.
		$local_css = wpm_apply_filters_typed( 'string', 'rocket_css_content', $local_css );

		$end_time = microtime( true );
		$duration = $end_time - $start_time;

		// Add for test purpose.
		Logger::debug( "Font download and optimization duration in seconds -- $duration", [ 'Host Fonts Locally' ] );
		Logger::debug( "Number of fonts downloaded for $css_url -- $count_fonts", [ 'Host Fonts Locally' ] );
		Logger::debug( 'Average download time per font -- ' . ( $count_fonts ? $download_average / $count_fonts : 0 ), [ 'Host Fonts Locally' ] );

		return $this->write_file( $css_filepath, $local_css );
	}

	/**
	 * Gets the normalized path of a font file relative to the fonts directory.
	 *
	 * @param string $font_url       Remote font URL.
	 * @param string $fonts_basepath Absolute path to the fonts directory.
	 *
	 * @return string Path starting with "/", or an empty string when the font can't be stored locally.
	 */
	private function get_local_font_path( string $font_url, string $fonts_basepath ): string {
		$path = wp_parse_url( $font_url, PHP_URL_PATH );

		if ( ! is_string( $path ) || '' === $path ) {
			return '';
		}

		$path = str_replace( '\\', '/', $path );

		if ( ! preg_match( self::FONT_PATH_CHARS, $path ) ) {
			return '';
		}

		// Rooting at "/" clamps leading ".." segments at the fonts directory, on POSIX and Windows alike.
		$path = '/' . ltrim( rocket_realpath( '/' . $path ), '/' );

		if ( ! preg_match( self::LOCAL_FONT_PATH, $path ) ) {
			return '';
		}

		if ( 0 !== strpos( rocket_realpath( $fonts_basepath . $path ), rocket_realpath( $fonts_basepath ) . '/' ) ) {
			return '';
		}

		return $path;
	}

	/**
	 * Checks if the content starts with a known font file signature.
	 *
	 * Embedded OpenType has no leading signature: it needs an .eot path, the "LP" magic number at byte 34
	 * and a little-endian size header matching the content length, and must not start like markup.
	 *
	 * @param string $content File content.
	 * @param string $path    Local font path.
	 *
	 * @return bool
	 */
	private function is_font_content( string $content, string $path ): bool {
		if ( in_array( substr( $content, 0, 4 ), self::FONT_SIGNATURES, true ) ) {
			return true;
		}

		if (
			'.eot' !== strtolower( substr( $path, -4 ) )
			|| strlen( $content ) < 36
			|| 'LP' !== substr( $content, 34, 2 )
			|| preg_match( '/^[\x09\x0A\x0C\x0D\x20<]/', $content )
		) {
			return false;
		}

		$size = unpack( 'V', $content );

		return is_array( $size ) && strlen( $content ) === $size[1];
	}

	/**
	 * Gets the remote content of the URL
	 *
	 * @param string $url URL to request content for.
	 *
	 * @return string
	 */
	private function get_remote_content( string $url ): string {
		$response = wp_safe_remote_get(
			$url,
			[
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
				'httpversion' => '2.0',
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Get the absolute path for a file
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $path               Path to the file.
	 *
	 * @return string
	 */
	private function get_absolute_path( string $font_provider_path, string $path ): string {
		return $this->path . $font_provider_path . $path;
	}

	/**
	 * Get the fonts relative paths
	 *
	 * @param string $font_provider_path Font provider path.
	 * @param string $path               Path to the file.
	 *
	 * @return string
	 */
	private function get_fonts_relative_path( string $font_provider_path, string $path ): string {
		$base_path      = $this->path . $font_provider_path . 'fonts';
		$wp_content_dir = rocket_get_constant( 'WP_CONTENT_DIR', '' );
		$relative_path  = str_replace( $wp_content_dir, '', $base_path );

		return $relative_path . $path;
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
		$dir = $this->get_absolute_path( $this->get_font_provider_path( $url ), $url );

		return $this->delete_file( $dir );
	}
}
