<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Frontend;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Media\PreloadFonts\Frontend\Controller as PreloadFonts;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;

class Subscriber implements Subscriber_Interface {

	/**
	 * Preload Fonts controller instance.
	 *
	 * @var PreloadFonts
	 */
	private $preload_fonts;

	/**
	 * DataManager instance
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Subscriber constructor.
	 *
	 * @param PreloadFonts $preload_fonts Preload Fonts controller instance.
	 * @param DataManager  $data_manager DataManager instance.
	 */
	public function __construct( PreloadFonts $preload_fonts, DataManager $data_manager ) {
		$this->preload_fonts = $preload_fonts;
		$this->data_manager  = $data_manager;
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
			'rocket_head_items'                   => [ 'add_preload_fonts_in_head', 30 ],
			'rocket_preload_fonts_excluded_fonts' => 'get_exclusions',
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
	 * Gets the list of fonts to be excluded from preloading.
	 * Merges any existing exclusions with those from the dynamic lists.
	 *
	 * @param array $exclusions Array of font URLs to be excluded from preloading.
	 * @return array
	 */
	public function get_exclusions( array $exclusions ): array {
		$lists = $this->data_manager->get_lists();
		$lists = isset( $lists->preload_fonts_exclusions ) ? $lists->preload_fonts_exclusions : [];

		// Check that lists is a valid array.
		if ( ! is_array( $lists ) ) {
			$lists = [];
		}

		// Return early if exlusions is empty.
		if ( empty( $exclusions ) ) {
			return $lists;
		}

		// Return early if lists is empty.
		if ( empty( $lists ) ) {
			return $exclusions;
		}

		// Only merge if exclusion and list is not empty.
		return array_merge( $exclusions, $lists );
	}
}
