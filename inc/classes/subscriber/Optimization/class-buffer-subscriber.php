<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Buffer\Optimization;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Event subscriber to buffer and process a page content.
 *
 * @since  3.3
 */
class Buffer_Subscriber implements Subscriber_Interface {
	/**
	 * Optimization instance
	 *
	 * @var Optimization
	 */
	private $optimizer;

	/**
	 * Constructor
	 *
	 * @param Optimization $optimizer Optimization instance.
	 */
	public function __construct( Optimization $optimizer ) {
		$this->optimizer = $optimizer;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'template_redirect' => [
				[ 'redirect_canonical', 1 ],
				[ 'start_content_process', 2 ]
			],
		];
	}

	/**
	 * Start buffering the page content and apply optimizations if we can.
	 *
	 * @since  3.3
	 *
	 * @return void
	 */
	public function start_content_process() {
		$this->optimizer->maybe_init_process();
	}

	/**
	 * Runs the main redirect_canonical function to redirect visitor to proper page url based on permalink if it has trailing slash or not.
	 *
	 * @since 3.8
	 *
	 * @return void
	 */
	public function redirect_canonical() {
		if ( ! function_exists( 'redirect_canonical' ) ){
			return;
		}

		remove_action( 'template_redirect', 'redirect_canonical' );
		redirect_canonical();
	}
}
