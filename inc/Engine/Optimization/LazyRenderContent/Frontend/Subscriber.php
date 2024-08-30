<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * LazyRenderContent controller.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Subscriber constructor.
	 *
	 * @param Controller $controller LazyRenderContent controller.
	 */
	public function __construct( Controller $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_buffer'                           => [ 'add_hashes', 16 ],
			'rocket_critical_image_saas_visit_buffer' => [ 'add_hashes', 16 ],
		];
	}

	/**
	 * Add hashes to the HTML elements
	 *
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_hashes( $html ) {
		return $this->controller->add_hashes( $html );
	}
}
