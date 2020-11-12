<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Admin\Options_Data;

class DeferJS {
	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Add the exclude defer JS option in WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_option( array $options ) : array {
		$options['exclude_defer_js'] = [];

		return $options;
	}

	/**
	 * Defer all JS files.
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_js( string $html ) : string {
		if ( ! $this->can_defer_js() ) {
			return $html;
		}

		$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		preg_match_all( '#<script\s+(?<before>[^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+)\s*?[\'"](?<after>[^>]+)?\/?>#i', $buffer_nocomments, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return $html;
		}

		$exclude_defer_js = implode( '|', $this->get_excluded() );

		foreach ( $matches as $tag ) {
			if ( preg_match( '#(' . $exclude_defer_js . ')#i', $tag['url'] ) ) {
				continue;
			}

			if ( false !== strpos( $tag['before'], ' async' ) || false !== strpos( $tag['after'], ' async' ) ) {
				continue;
			}

			if ( false !== strpos( $tag['before'], ' defer' ) || false !== strpos( $tag['after'], ' defer' ) ) {
				continue;
			}

			$deferred_tag = str_replace( '>', ' defer>', $tag );
			$html         = str_replace( $tag, $deferred_tag, $html );
		}

		return $html;
	}

	/**
	 * Checks if we can defer JS
	 *
	 * @since 3.8
	 *
	 * @return boolean
	 */
	private function can_defer_js() : bool {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! $this->options->get( 'defer_all_js', 0 ) ) {
			return false;
		}

		return ! is_rocket_post_excluded_option( 'defer_all_js' );
	}

	/**
	 * Get list of JS files to be excluded from defer JS.
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	private function get_excluded() : array {
		$exclude_defer_js = [
			'gist.github.com',
			'content.jwplatform.com',
			'js.hsforms.net',
			'www.uplaunch.com',
			'google.com/recaptcha',
			'widget.reviews.co.uk',
			'verify.authorize.net/anetseal',
			'lib/admin/assets/lib/webfont/webfont.min.js',
			'app.mailerlite.com',
			'widget.reviews.io',
			'simplybook.(.*)/v2/widget/widget.js',
			'/wp-includes/js/dist/i18n.min.js',
			'/wp-content/plugins/wpfront-notification-bar/js/wpfront-notification-bar(.*).js',
			'/wp-content/plugins/oxygen/component-framework/vendor/aos/aos.js',
			'static.mailerlite.com/data/(.*).js',
			'cdn.voxpow.com/static/libs/v1/(.*).js',
			'cdn.voxpow.com/media/trackers/js/(.*).js',
		];

		if ( $this->options->get( 'defer_all_js', 0 ) && $this->options->get( 'defer_all_js_safe', 0 ) ) {
			$exclude_defer_js = array_merge( $exclude_defer_js, $this->get_jquery_urls() );
		}

		$exclude_defer_js = array_unique( array_merge( $exclude_defer_js, $this->options->get( 'exclude_defer_js', [] ) ) );

		/**
		 * Filter list of Deferred JavaScript files
		 *
		 * @since 2.10
		 *
		 * @param array $exclude_defer_js An array of URLs for the JS files to be excluded.
		 */
		$exclude_defer_js = apply_filters( 'rocket_exclude_defer_js', $exclude_defer_js );

		foreach ( $exclude_defer_js as $i => $exclude ) {
			$exclude_defer_js[ $i ] = str_replace( '#', '\#', $exclude );
		}

		return $exclude_defer_js;
	}

	/**
	 * Returns jquery URLs to not defer when Safe mode is enabled
	 *
	 * @since 3.8
	 *
	 * @return array
	 */
	private function get_jquery_urls() : array {
		$jquery_urls = [
			'c0.wp.com/c/(?:.+)/wp-includes/js/jquery/jquery.js',
			'ajax.googleapis.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
			'cdnjs.cloudflare.com/ajax/libs/jquery/(?:.+)/jquery(?:\.min)?.js',
			'code.jquery.com/jquery-.*(?:\.min|slim)?.js',
		];

		if ( isset( wp_scripts()->registered['jquery-core']->src ) ) {
			$jquery_urls[] = rocket_clean_exclude_file( site_url( wp_scripts()->registered['jquery-core']->src ) );
		}

		return $jquery_urls;
	}
}
