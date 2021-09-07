<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * HTML instance.
	 *
	 * @since 3.7
	 *
	 * @var HTML
	 */
	private $html;

	/**
	 * WP_Filesystem_Direct instance.
	 *
	 * @since 3.7
	 *
	 * @var \WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Script enqueued status.
	 *
	 * @since 3.7
	 *
	 * @var bool
	 */
	private $is_enqueued = false;

	/**
	 * Subscriber constructor.
	 *
	 * @param HTML                  $html HTML Instance.
	 * @param \WP_Filesystem_Direct $filesystem The Filesystem object.
	 * @param Options_Data          $options Options data instance.
	 */
	public function __construct( HTML $html, $filesystem, Options_Data $options ) {
		$this->html       = $html;
		$this->filesystem = $filesystem;
		$this->options    = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.7
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'                               => [
				[ 'delay_js', 26 ],
				[ 'add_delay_js_script', 26 ],
			],
			'pre_get_rocket_option_minify_concatenate_js' => 'maybe_disable_option',
		];
	}

	/**
	 * Modifies scripts HTML to apply delay JS attribute
	 *
	 * @since 3.7
	 *
	 * @param string $buffer_html Html for the page.
	 *
	 * @return string
	 */
	public function delay_js( $buffer_html ) {
		return $this->html->delay_js( $buffer_html );
	}

	/**
	 * Displays the inline script to the head when the option is enabled.
	 *
	 * @since 3.9 Hooked on rocket_buffer, display the script right after <head>
	 * @since 3.7
	 *
	 * @param string $html HTML content.
	 *
	 * @return string
	 */
	public function add_delay_js_script( $html ): string {
		if ( ! $this->html->is_allowed() ) {
			return $html;
		}
		$pattern         = '/<head[^>]*>/i';
		$lazyload_script = $this->filesystem->get_contents( rocket_get_constant( 'WP_ROCKET_PATH' ) . 'assets/js/lazyload-scripts.min.js' );
		$replace_pattern = "$0<wpr_add_ie_fall_back_here><script>{$lazyload_script}</script>";

		$match_pattern = "#(<head[^>]*>(.|\n)*)(<meta[ ]+(http-equiv=[\'\" ]Content-Type[\'\" ][^>]*|)(charset=[\'\" ]*([^\'\"> ][^\'\">]+[^\'\"> ])[\'\" ]*|charset=[ ]*([^\'\"> ][^\'\">]+[^\'\"> ]))([^>]*|)>)((.|\n)*</head>)#i";

		if ( (bool) preg_match( $match_pattern, $html, $regs ) !== false ) {
			$pattern         = $match_pattern;
			$replace_pattern = "$1$3<wpr_add_ie_fall_back_here><script>{$lazyload_script}</script>$9";
		}

		if ( false !== $lazyload_script ) {
			$html = preg_replace( $pattern, $replace_pattern, $html, 1 );
		}

		return preg_replace( '/<wpr_add_ie_fall_back_here>/i', '$1<script>' . $this->html->get_ie_fallback() . '</script>', $html, 1 );
	}

	/**
	 * Disables defer JS if delay JS is enabled
	 *
	 * @since 3.9
	 *
	 * @param null $value Original value. Should be always null.
	 *
	 * @return null|false
	 */
	public function maybe_disable_option( $value ) {
		if ( $this->html->is_allowed() ) {
			return false;
		}

		return $value;
	}
}
