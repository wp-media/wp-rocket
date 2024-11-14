<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Media\Fonts\Context\Context;
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
	 * Base url.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Constructor.
	 *
	 * @param Context $context Context instance.
	 */
	public function __construct( Context $context ) {
		$this->context  = $context;
		$this->base_url = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_URL', '' ) . 'fonts/' . get_current_blog_id() . '/';
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
			$html = $this->replace_font( $font, $html );
		}

		foreach ( $v2_fonts as $font ) {
			$html = $this->replace_font( $font, $html );
		}

		$html = $this->remove_preconnect_and_prefetch( $html );

		return $html;
	}

	/**
	 * Replaces the Google Fonts URL with the local one.
	 *
	 * @param array  $font Font data.
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	private function replace_font( $font, $html ): string {
		$hash  = md5( $font['url'] );
		$local = $this->get_optimized_markup( $hash, $font['url'] );

		return str_replace( $font[0], $local, $html );
	}

	/**
	 * Returns the optimized markup for Google Fonts
	 *
	 * @since 3.18
	 *
	 * @param string $hash Font Url has.
	 * @param string $original_url Fonts Url.
	 *
	 * @return string
	 */
	protected function get_optimized_markup( string $hash, string $original_url ): string {
		$levels = 3;
		$base   = substr( $hash, 0, $levels );
		$remain = substr( $hash, $levels );

		$path_array   = str_split( $base );
		$path_array[] = $remain;

		$path = implode( '/', $path_array );
		$url  = $this->base_url . $path . '.css';

		$gf_parameters = wp_parse_url( $original_url, PHP_URL_QUERY );

		return sprintf(
			'<link rel="stylesheet" href="%1$s" data-wpr-hosted-gf-parameters="%2$s"/>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$url,
			$gf_parameters
		);
	}

	/**

	 * Removes preconnect and prefetch links for Google Fonts from the HTML content.
	 *
	 * @param string $html HTML content.
	 *
	 * @return string Modified HTML content without preconnect and prefetch links.
	 */
	private function remove_preconnect_and_prefetch( string $html ) {
		/**
		 * Filters the removal of Google preconnect and prefetch links.
		 *
		 * @since 3.18
		 *
		 * @param bool $enable_noscript Enable or disable noscript tag.
		 */
		$remove_links = wpm_apply_filters_typed( 'boolean', 'rocket_remove_font_pre_links', true );

		if ( ! $remove_links ) {
			return $html;
		}

		$pattern = '/<link(?:[^>]*)(?:rel=["\'](?:dns-prefetch|preconnect)["\'])(?:[^>]*)(?:href=["\'](?:https?:)?\/\/(?:fonts\.(?:googleapis|gstatic)\.com)["\'])(?:[^>]*)>/i';

		$html = preg_replace( $pattern, '', $html );

		return $html;
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
}
