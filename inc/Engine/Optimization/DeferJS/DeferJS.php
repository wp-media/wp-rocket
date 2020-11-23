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

		preg_match_all( '#<script\s+(?:[^>]+[\s\'"])?src\s*=\s*[\'"]\s*?(?<url>[^\'"]+)\s*?[\'"](?:[^>]+)?\/?>#i', $buffer_nocomments, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return $html;
		}

		$exclude_defer_js = implode( '|', $this->get_excluded() );

		foreach ( $matches as $tag ) {
			if ( preg_match( '#(' . $exclude_defer_js . ')#i', $tag['url'] ) ) {
				continue;
			}

			if ( preg_match( '/\s+(?:async|defer)/i', $tag[0] ) ) {
				continue;
			}

			$deferred_tag = str_replace( '>', ' defer>', $tag[0] );
			$html         = str_replace( $tag[0], $deferred_tag, $html );
		}

		return $html;
	}

	/**
	 * Defers inline JS containing jQuery calls
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_inline_js( string $html ) : string {
		if ( ! $this->can_defer_js() ) {
			return $html;
		}

		$exclude_defer_js = implode( '|', $this->get_excluded() );

		if ( preg_match( '/(jquery(?:.*)?\.js)/i', $exclude_defer_js ) ) {
			return $html;
		}

		$buffer_nocomments = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		preg_match_all( '#<script(?:[^>]*)>(?<content>[\s\S]*?)</script>#msi', $buffer_nocomments, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return $html;
		}

		foreach ( $matches as $inline_js ) {
			if ( empty( $inline_js['content'] ) ) {
				continue;
			}

			if ( ! preg_match( '/(jQuery|\$\.\(|\$\()/msi', $inline_js['content'] ) ) {
				continue;
			}

			if ( preg_match( '/(DOMContentLoaded|document\.write)/msi', $inline_js['content'] ) ) {
				continue;
			}

			$tag  = str_replace( $inline_js['content'], $this->inline_js_wrapper( $inline_js['content'] ), $matches[0] );
			$html = str_replace( $matches[0], $tag, $html );
		}

		return $html;
	}

	/**
	 * Adds the JS wrapper for inline jQuery code
	 *
	 * @since 3.8
	 *
	 * @param string $content Inline JS content.
	 * @return string
	 */
	private function inline_js_wrapper( string $content ) : string {
		return "window.addEventListener('DOMContentLoaded', function() {" . $content . '});';
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
	public function get_excluded() : array {
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
}
