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

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.18
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_host_google_fonts' => [ 'process', 15, 3 ],
		];
	}

	/**
	 * Save Fonts locally
	 *
	 * @param string  $font_url The url of the font.
	 * @param string  $provider The font provider.
	 * @param integer $version  The version of the font.
	 *
	 * @return void
	 */
	public function process( string $font_url, string $provider, int $version ): void {
		$this->fonts->process( $font_url, $provider, $version );
	}
}
