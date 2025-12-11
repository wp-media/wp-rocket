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
			'rocket_enable_rucss_fonts_preload'   => 'disable_rucss_preload_fonts',
			'rocket_preload_fonts_excluded_fonts' => 'get_exclusions',
			'rocket_external_font_exclusions'     => 'get_external_font_exclusions',
			'rocket_buffer'                       => 'maybe_remove_existing_preloaded_fonts',
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
	public function disable_rucss_preload_fonts( $status ): bool {
		return $this->preload_fonts->disable_rucss_preload_fonts( $status );
	}

	/**
	 * Gets the list of fonts to be excluded from preloading.
	 * Merges any existing exclusions with those from the dynamic lists.
	 *
	 * @param array $exclusions Array of font URLs to be excluded from preloading.
	 * @return array
	 */
	public function get_exclusions( array $exclusions ): array {
		$lists = $this->data_manager->get_lists()->preload_fonts_exclusions ?? [];
		/**
		 * Merge exclusions and lists.
		 * Handle empty arrays gracefully.
		 */
		return array_merge( $exclusions, (array) $lists );
	}

	/**
	 * Get external font exclusions for beacon configuration
	 *
	 * @since 3.19.1
	 *
	 * @param array $exclusions Array of domains to exclude from external font processing.
	 * @return array
	 */
	public function get_external_font_exclusions( array $exclusions ): array {
		// Merge with dynamic lists exclusions.
		$lists = $this->data_manager->get_lists()->external_font_exclusions ?? [];

		return array_merge( $exclusions, (array) $lists );
	}

	/**
	 * Removes existing preloaded font tags from the HTML buffer.
	 *
	 * @param string $html The HTML content.
	 * @return string Modified HTML content.
	 */
	public function maybe_remove_existing_preloaded_fonts( string $html ): string {
		return $this->preload_fonts->maybe_remove_existing_preloaded_fonts( $html );
	}
}
