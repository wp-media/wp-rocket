<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Frontend;

use WP_Rocket\Engine\Media\Fonts\Controller\Fonts;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Fonts instance
	 *
	 * @var Fonts
	 */
	private $fonts;

	/**
	 * Instantiate the class
	 *
	 * @param Fonts $fonts Fonts instance.
	 */
	public function __construct( Fonts $fonts ) {
		$this->fonts = $fonts;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_buffer' => [ 'process', 15 ],
		];
	}

	public function process( $html ): string {
		return $this->fonts->process( $html );
	}
}
