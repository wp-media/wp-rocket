<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Controller as PreloadFonts;

class Subscriber implements Subscriber_Interface {

	/**
	 * Preload Fonts controller instance.
	 *
	 * @var PreloadFonts
	 */
	private $preload_fonts;

	/**
	 * Subscriber constructor.
	 *
	 * @param PreloadFonts $preload_fonts Preload Fonts controller instance.
	 */
	public function __construct( PreloadFonts $preload_fonts ) {
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
			'rocket_head_items'                 => [ 'add_preload_fonts_in_head', 30 ],
			'rocket_enable_rucss_fonts_preload' => 'disable_rucss_preload_fonts',
		];
	}

	/**
	 * Add preload fonts into head.
	 *
	 * @param array $items Head items.
	 * @return array
	 */
	public function add_preload_fonts_in_head( $items ) {
		return $this->preload_fonts->add_preload_fonts_in_head( $items );
	}

	/**
	 * Disables the preloading of fonts by the Remove Unused CSS (RUCSS) feature.
	 *
	 * This method is used to prevent RUCSS from preloading fonts when certain conditions are met.
	 *
	 * @param bool $status The current status of font preloading.
	 * @return bool Modified status indicating whether font preloading should be disabled.
	 */
	public function disable_rucss_preload_fonts( $status ) {
		$this->preload_fonts->disable_rucss_preload_fonts( $status );
	}
}
