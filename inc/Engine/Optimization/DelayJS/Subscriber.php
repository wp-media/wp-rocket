<?php

namespace WP_Rocket\Engine\Optimization\DelayJS;

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
	 * Subscriber constructor.
	 *
	 * @param HTML                  $html HTML Instance.
	 * @param \WP_Filesystem_Direct $filesystem The Filesystem object.
	 */
	public function __construct( HTML $html, $filesystem ) {
		$this->html       = $html;
		$this->filesystem = $filesystem;
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
			'rocket_buffer'      => [
				[ 'delay_js', 21 ],
			],
			'wp_enqueue_scripts' => 'add_delay_js_script',
		];
	}

	/**
	 * Using html buffer get scripts to be delayed and adjust their html.
	 *
	 * @param string $buffer_html Html for the page.
	 *
	 * @return string
	 */
	public function delay_js( $buffer_html ) {
		return $this->html->delay_js( $buffer_html );
	}

	/**
	 * Adds the inline script to the footer when the option is enabled.
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function add_delay_js_script() {
		if ( ! $this->html->is_allowed() ) {
			return;
		}

		// Register handle with no src to add the inline script after.
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_register_script(
			'rocket-delay-js',
			'',
			[],
			'',
			true
		);
		wp_enqueue_script( 'rocket-delay-js' );

		$script_filename = rocket_get_constant( 'SCRIPT_DEBUG' ) ? 'lazyload-scripts.js' : 'lazyload-scripts.min.js';

		wp_add_inline_script(
			'rocket-delay-js',
			$this->filesystem->get_contents( rocket_get_constant( 'WP_ROCKET_PATH' ) . "assets/js/{$script_filename}" )
		);
	}

}
