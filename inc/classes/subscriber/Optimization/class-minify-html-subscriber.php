<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data as Options;
use MatthiasMullie\Minify;

/**
 * HTML minification subscriber
 *
 * @since 3.1
 * @author Remy Perona
 */
class Minify_HTML_Subscriber implements Subscriber_Interface {
	/**
	 * Plugin options
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Options $options Plugin options.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'process', 21 ],
		];
	}

	/**
	 * Minifies HTML
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function process( $html ) {
		if ( ! $this->options->get( 'minify_html' ) || \is_rocket_post_excluded_option( 'minify_html' ) ) {
			return $html;
		}

		$html_options = [
			'cssMinifier' => [ $this, 'minify_inline_css' ],
		];

		/**
		 * Filter options of minify inline HTML
		 *
		 * @since 1.1.12
		 *
		 * @param array $html_options Options of minify inline HTML.
		 */
		$html_options = apply_filters( 'rocket_minify_html_options', $html_options );

		return \Minify_HTML::minify( $html, $html_options );
	}

	/**
	 * Minifies inline CSS
	 *
	 * @since 1.1.6
	 *
	 * @param string $css HTML content.
	 * @return string
	 */
	public function minify_inline_css( $css ) {
		$minify = new Minify\CSS( $css );
		return $minify->minify();
	}

	/**
	 * Minifies inline JavaScript
	 *
	 * @since 1.1.6
	 *
	 * @param string $javascript HTML content.
	 * @return string
	 */
	public function minify_inline_js( $javascript ) {
		$minify = new Minify\JS( $javascript );
		return $minify->minify();
	}
}
