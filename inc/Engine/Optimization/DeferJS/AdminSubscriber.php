<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber implements Subscriber_Interface {
	/**
	 * DeferJS instance
	 *
	 * @var DeferJS
	 */
	private $defer_js;

	/**
	 * Instantiate the class
	 *
	 * @param DeferJS $defer_js DeferJS instance.
	 */
	public function __construct( DeferJS $defer_js ) {
		$this->defer_js = $defer_js;
	}

	/**
	 * Returns array of events this listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_first_install_options' => 'add_defer_js_option',
		];
	}

	/**
	 * Adds defer js option to WP Rocket options array
	 *
	 * @since 3.8
	 *
	 * @param array $options WP Rocket options array.
	 * @return array
	 */
	public function add_defer_js_option( array $options ) : array {
		return $this->defer_js->add_option( $options );
	}
}
