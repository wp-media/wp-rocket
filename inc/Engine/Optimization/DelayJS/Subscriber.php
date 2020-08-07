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
	 * Subscriber constructor.
	 *
	 * @param HTML $html HTML Instance.
	 */
	public function __construct( HTML $html ) {
		$this->html = $html;
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
			'rocket_buffer' => [
				[ 'delay_js', 21 ],
			],
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

}
