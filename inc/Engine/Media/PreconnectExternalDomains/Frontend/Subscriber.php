<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @param Controller $controller Controller instance.
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
			'rocket_head_items' => [ 'preconnect_domains', 10 ],
		];
	}

	/**
	 * Preconnect current page domains into head.
	 *
	 * @param array $items Head items.
	 * @return array
	 */
	public function preconnect_domains( array $items ) {
		return $this->controller->add_preconnect_to_head( $items );
	}
}
