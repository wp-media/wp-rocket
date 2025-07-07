<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Common\Head\ElementTrait;
use WP_Rocket\Engine\Media\Fonts\Context\OptimizationContext;
use WP_Rocket\Engine\Media\Fonts\Context\SaasContext;
use WP_Rocket\Engine\Media\Fonts\Filesystem;
use WP_Rocket\Engine\Optimization\RegexTrait;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Engine\Media\Fonts\FontsTrait;

class Controller {
	use RegexTrait;
	use FontsTrait;
	use ElementTrait;

	/**
	 * Optimization Context instance.
	 *
	 * @var OptimizationContext
	 */
	private $optimization_context;

	/**
	 * SaaS Context instance.
	 *
	 * @var SaasContext
	 */
	private $saas_context;

	/**
	 * Filesystem instance.
	 *
	 * @var Filesystem
	 */
	private $filesystem;

	/**
	 * Base url.
	 *
	 * @var string
	 */
	private $base_url;

	/**
	 * Base path.
	 *
	 * @var string
	 */
	private $base_path;

	/**
	 * Error flag.
	 *
	 * @var bool
	 */
	private $error = false;

	/**
	 * Constructor.
	 *
	 * @param OptimizationContext $optimization_context Optimization Context instance.
	 * @param SaasContext         $saas_context SaaS Context instance.
	 * @param Filesystem          $filesystem Filesystem instance.
	 */
	public function __construct( OptimizationContext $optimization_context, SaasContext $saas_context, Filesystem $filesystem ) {
		$this->optimization_context = $optimization_context;
		$this->saas_context         = $saas_context;
		$this->base_path            = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_PATH', '' ) . 'fonts/' . get_current_blog_id() . '/';
		$this->base_url             = rocket_get_constant( 'WP_ROCKET_CACHE_ROOT_URL', '' ) . 'fonts/' . get_current_blog_id() . '/';
		$this->filesystem           = $filesystem;
	}

	/**
	 * Rewrites the Google Fonts paths to local ones.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	private function rewrite_fonts( $html ): string {
		// For test purposes.
		$start_time = microtime( true );

		$html_nocomments = $this->hide_comments( $html );

		$v1_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css[^\d](?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );
		$v2_fonts = $this->find( '<link(?:\s+(?:(?!href\s*=\s*)[^>])+)?(?:\s+href\s*=\s*([\'"])(?<url>(?:https?:)?\/\/fonts\.googleapis\.com\/css2(?:(?!\1).)+)\1)(?:\s+[^>]*)?>', $html_nocomments );

		if ( ! $v1_fonts && ! $v2_fonts ) {
			Logger::debug( 'No Google Fonts found.', [ 'Host Fonts Locally' ] );
			return $html;
		}

		$exclusions = $this->get_exclusions();

		// Count fonts - for test purposes.
		$total_v1    = count( $v1_fonts );
		$total_v2    = count( $v2_fonts );
		$total_fonts = $total_v1 + $total_v2;

		foreach ( $v1_fonts as $font ) {
			if ( $this->is_excluded( $font[0], $exclusions ) ) {
				continue;
			}
			$html = $this->replace_font( $font, $html );
		}

		foreach ( $v2_fonts as $font ) {
			if ( $this->is_excluded( $font[0], $exclusions ) ) {
				continue;
			}
			$html = $this->replace_font( $font, $html );
		}

		if ( ! $this->error ) {
			$html = $this->remove_preconnect_and_prefetch( $html );
		}

		// End time measurement.
		$end_time = microtime( true );

		// Log the total execution time and number of fonts processed, with breakdown.
		$duration = $end_time - $start_time;
		Logger::debug( "Total execution time for Host Google Fonts Feature in seconds -- $duration. CSS files processed: $total_fonts | Total v1: $total_v1 | Total v2: $total_v2", [ 'Host Fonts Locally' ] );

		return $html;
	}

	/**
	 * Rewrite fonts for normal optimizations.
	 *
	 * @param string $html page HTML.
	 * @return string
	 */
	public function rewrite_fonts_for_optimizations( $html ): string {
		if ( ! $this->optimization_context->is_allowed() ) {
			return $html;
		}
		return $this->rewrite_fonts( $html );
	}

	/**
	 * Rewrite fonts for SaaS visits optimizations.
	 *
	 * @param string $html page HTML.
	 * @return string
	 */
	public function rewrite_fonts_for_saas( $html ): string {
		if ( ! $this->saas_context->is_allowed() ) {
			return $html;
		}
		return $this->rewrite_fonts( $html );
	}

	/**
	 * Replaces the Google Fonts URL with the local one.
	 *
	 * @param array  $font    Font data.
	 * @param string $html    HTML content.
	 * @param string $font_provider Font provider.
	 *
	 * @return string
	 */
	private function replace_font( array $font, string $html, string $font_provider = 'google-fonts' ): string {
		$hash = md5( $font['url'] );

		if ( $this->filesystem->exists( $this->get_css_path( $hash, $font_provider ) ) ) {
			$local = $this->get_optimized_markup( $hash, $font['url'], $font_provider );

			return str_replace( $font[0], $local, $html );
		}

		if ( ! $this->filesystem->write_font_css( $font['url'], $font_provider ) ) {
			$this->error = true;

			return $html;
		}

		$local = $this->get_optimized_markup( $hash, $font['url'], $font_provider );

		return str_replace( $font[0], $local, $html );
	}

	/**
	 * Returns the optimized markup for Google Fonts
	 *
	 * @since 3.18
	 *
	 * @param string $hash Font Url has.
	 * @param string $original_url Fonts Url.
	 * @param string $font_provider Fonts provider.
	 *
	 * @return string
	 */
	private function get_optimized_markup(
		string $hash,
		string $original_url,
		string $font_provider
	): string {
		$font_provider_path = sprintf( '%s/', $font_provider );

		$original_url  = html_entity_decode( $original_url, ENT_QUOTES );
		$gf_parameters = wp_parse_url( $original_url, PHP_URL_QUERY );

		if ( $this->is_host_fonts_inline_css() ) {
			$local_css_path = $this->get_css_path( $hash, $font_provider );

			$inline_css = $this->get_font_inline_css( $local_css_path, $gf_parameters );

			if ( ! empty( $inline_css ) ) {
				return $inline_css;
			}
		}

		// This filter is documented in inc/classes/optimization/css/class-abstract-css-optimization.php.
		$url = wpm_apply_filters_typed( 'string', 'rocket_css_url', $this->base_url . $font_provider_path . 'css/' . $this->filesystem->hash_to_path( $hash ) . '.css' );

		return sprintf(
			'<link rel="stylesheet" href="%1$s" data-wpr-hosted-gf-parameters="%2$s"/>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
			$url,
			$gf_parameters
		);
	}

	/**
	 * Check if we need to show local font styles as inline or not.
	 *
	 * @return bool
	 */
	private function is_host_fonts_inline_css() {
		/**
		 * Filters to enable the inline css output.
		 *
		 * @since 3.18
		 *
		 * @param bool $enable Tells if we are enabling or not the inline css output.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_host_fonts_locally_inline_css', false );
	}

	/**
	 * Get optimized local url.
	 *
	 * @param string $hash Font url hash.
	 * @param string $font_provider Font provider.
	 * @return string
	 */
	private function get_optimized_url( string $hash, string $font_provider ) {
		$font_provider_path = sprintf( '%s/', $font_provider );

		// This filter is documented in inc/classes/optimization/css/class-abstract-css-optimization.php.
		return wpm_apply_filters_typed( 'string', 'rocket_css_url', $this->base_url . $font_provider_path . 'css/' . $this->filesystem->hash_to_path( $hash ) . '.css' );
	}

	/**
	 * Gets the CSS path for the font.
	 *
	 * @param string $hash Font hash.
	 * @param string $font_provider Font provider.
	 *
	 * @return string
	 */
	private function get_css_path( string $hash, string $font_provider ): string {
		$font_provider_path = sprintf( '%s/', $font_provider );

		return $this->base_path . $font_provider_path . 'css/' . $this->filesystem->hash_to_path( $hash ) . '.css';
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
		 * Filters the removal of Google preconnect/prefetch links.
		 *
		 * @since 3.18
		 *
		 * @param bool $enable_removal Enable or disable removal of Google preconnect/prefetch links.
		 */
		$remove_links = wpm_apply_filters_typed( 'boolean', 'rocket_remove_font_pre_links', true );

		if ( ! $remove_links ) {
			return $html;
		}

		$pattern = '/<link[^>]*\b(rel\s*=\s*[\'"](?:preconnect|dns-prefetch)[\'"]|href\s*=\s*[\'"](?:https?:)?\/\/(?:fonts\.(?:googleapis|gstatic)\.com)[\'"])[^>]*\b(rel\s*=\s*[\'"](?:preconnect|dns-prefetch)[\'"]|href\s*=\s*[\'"](?:https?:)?\/\/(?:fonts\.(?:googleapis|gstatic)\.com)[\'"])[^>]*>/i';

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
		if ( ! $this->optimization_context->is_allowed() ) {
			return $disable;
		}

		return true;
	}

	/**
	 * Gets the font inline css.
	 *
	 * @param string $local_css_path CSS file path.
	 * @param string $gf_parameters Google Fonts parameters.
	 *
	 * @return string
	 */
	private function get_font_inline_css( string $local_css_path, string $gf_parameters ): string {
		$content = $this->filesystem->get_file_content( $local_css_path );

		if ( empty( $content ) ) {
			return '';
		}

		return sprintf(
			'<style data-wpr-hosted-gf-parameters="%1$s">%2$s</style>',
			$gf_parameters,
			$content
		);
	}

	/**
	 * Get local font url using the external one.
	 *
	 * @param string $font_url Font url.
	 * @param string $font_provider Font provider.
	 * @return string
	 */
	private function get_font_local_url( string $font_url, string $font_provider = 'google-fonts' ): string {
		$hash = md5( $font_url );

		if ( $this->filesystem->exists( $this->get_css_path( $hash, $font_provider ) ) ) {
			return $this->get_optimized_url( $hash, $font_provider );
		}

		if ( ! $this->filesystem->write_font_css( $font_url, $font_provider ) ) {
			$this->error = true;

			return '';
		}

		return $this->get_optimized_url( $hash, $font_provider );
	}

	/**
	 * Is this a url of google font or not.
	 *
	 * @param string $url Font url to test.
	 * @return bool
	 */
	private function is_google_font_url( string $url ): bool {
		return ! empty( $this->find( '(?:https?:)?\/\/fonts\.googleapis\.com\/css.+', $url ) );
	}

	/**
	 * Get font CSS styles as inline using font url.
	 *
	 * @param string $font_url Font url.
	 * @param string $gf_parameters Google font query string.
	 * @param string $font_provider Font provider.
	 *
	 * @return array|string[]
	 */
	private function get_font_styles_by_url( string $font_url, string $gf_parameters, string $font_provider = 'google-fonts' ) {
		$hash           = md5( $font_url );
		$local_css_path = $this->get_css_path( $hash, $font_provider );

		$inline_css = $this->filesystem->get_file_content( $local_css_path );
		if ( empty( $inline_css ) ) {
			return [];
		}

		return $this->style_tag(
			$inline_css,
			[
				'data-wpr-hosted-gf-parameters' => $gf_parameters,
			]
		);
	}

	/**
	 * Rewrite fonts in head items.
	 *
	 * @param array $items Head items.
	 * @return array
	 */
	public function rewrite_fonts_in_head( array $items ): array {
		if ( ! $this->optimization_context->is_allowed() ) {
			return $items;
		}

		$exclusions = $this->get_exclusions();
		foreach ( $items as $key => &$item ) {
			if ( empty( $item['href'] ) || ! $this->is_google_font_url( $item['href'] ) ) {
				continue;
			}

			if ( $this->is_excluded( $item['href'], $exclusions ) ) {
				continue;
			}

			$font_url       = html_entity_decode( $item['href'], ENT_QUOTES );
			$gf_parameters  = wp_parse_url( $font_url, PHP_URL_QUERY );
			$local_font_url = $this->get_font_local_url( $item['href'] );

			if ( $this->is_host_fonts_inline_css() ) {
				$items[] = $this->get_font_styles_by_url( $item['href'], $gf_parameters );
				unset( $items[ $key ] );
				continue;
			}

			$item['href']                          = $local_font_url;
			$item['data-wpr-hosted-gf-parameters'] = $gf_parameters;
		}
		return $items;
	}
}
