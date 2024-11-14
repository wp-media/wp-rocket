<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Engine\Media\Fonts\Controller\Fonts;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;

class Controller {
	use RegexTrait;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;


	/**
	 * Font instance.
	 *
	 * @var Fonts
	 */
	private $font;

	/**
	 * Base url.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Constructor.
	 *
	 * @param Context $context Context instance.
	 * @param Fonts   $font   Font instance.
	 * @param string  $base_url Media font Base url.
	 */
	public function __construct( Context $context, Fonts $font, string $base_url ) {
		$this->context  = $context;
		$this->base_url = $base_url . get_current_blog_id() . '/';
		$this->font     = $font;
	}

	/**
	 * Rewrites the Google Fonts paths to local ones.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite_fonts( string $html ): string {
		if ( ! $this->context->is_allowed() ) {
			return $html;
		}

		$html_nocomments = $this->hide_comments( $html );

		$v1_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );
		$v2_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $v1_fonts && ! $v2_fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'Host Fonts Locally' ] );
			return $html;
		}

		foreach ( $v1_fonts as $font ) {
			$this->download_font( $font['url'], 1 );
			$html = $this->replace_font( $font, $html, 1 );
		}

		foreach ( $v2_fonts as $font ) {
			$this->download_font( $font['url'], 2 );
			$html = $this->replace_font( $font, $html, 2 );
		}

		return $html;
	}

	/**
	 * Replaces the Google Fonts URL with the local one.
	 *
	 * @param array  $font    Font data.
	 * @param string $html    HTML content.
	 * @param int    $version Provided font version.
	 * @param string $font_provider Font provider.
	 *
	 * @return string
	 */
	private function replace_font( $font, $html, int $version, string $font_provider = 'google-font' ): string {
		$hash  = md5( $font['url'] );
		$local = $this->get_optimized_markup( $hash, $font['url'], $version, $font_provider );

		return str_replace( $font[0], $local, $html );
	}

	/**
	 * Returns the optimized markup for Google Fonts
	 *
	 * @since 3.18
	 *
	 * @param string $hash Font Url has.
	 * @param string $original_url Fonts Url.
	 * @param int    $version Fonts version.
	 * @param string $font_provider Fonts provider.
	 *
	 * @return string
	 */
	protected function get_optimized_markup(
		string $hash,
		string $original_url,
		int $version,
		string $font_provider
	): string {
		$levels = 3;
		$base   = substr( $hash, 0, $levels );
		$remain = substr( $hash, $levels );

		$path_array   = str_split( $base );
		$path_array[] = $remain;

		$path               = implode( '/', $path_array );
		$font_provider_path = sprintf( '%s/%d/', $font_provider, $version );

		$url = $this->base_url . $font_provider_path . $path . '.css';

		$gf_parameters = wp_parse_url( $original_url, PHP_URL_QUERY );

		return sprintf(
			'<link rel="stylesheet" href="%1$s" data-wpr-hosted-gf-parameters="%2$s"/>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$url,
			$gf_parameters
		);
	}

	/**
	 * Disables the preload of Google Fonts.
	 *
	 * @param bool $disable Whether to disable the preload of Google Fonts.
	 *
	 * @return bool
	 */
	public function disable_google_fonts_preload( $disable ): bool {
		if ( ! $this->context->is_allowed() ) {
			return $disable;
		}

		return true;
	}

	/**
	 * Download font
	 *
	 * @param string $font_url Font url to be downloaded.
	 * @param int    $font_version The version of the font.
	 * @param string $provider  The font provider.
	 */
	private function download_font( string $font_url, int $font_version, string $provider = 'google-font' ): void {
		$this->font->process( $font_url, $provider, $font_version );
	}
}
