<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DeferJS;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
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
			'rocket_buffer' => [ 'defer_js', 24 ],
		];
	}

	/**
	 * Adds the defer attribute to JS files
	 *
	 * @since 3.8
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function defer_js( string $html ) : string {
		return $this->defer_js->defer_js( $html );
	}
}
