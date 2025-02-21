<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Controller as PreloadFonts;

class Subscriber implements Subscriber_Interface {

	/**
	 * @var PreloadFonts
	 */
	private $preload_fonts;

	/**
	 * @param PreloadFonts $preload_fonts
	 */
	public function __construct(PreloadFonts $preload_fonts) {
		$this->preload_fonts = $preload_fonts;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.19
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_head_items' => ['add_preload_fonts_in_head', 20],
		];
	}

	/**
	 * @param array $items
	 * @return array
	 */
	public function add_preload_fonts_in_head( $items ) {
		// Here we don't need this check, but we need to check if context->is_allowed returns true.
		if ( ! is_array( $items ) ) {
			$items = [];
		}

		return $this->preload_fonts->add_preload_fonts_in_head( $items );
	}
}
