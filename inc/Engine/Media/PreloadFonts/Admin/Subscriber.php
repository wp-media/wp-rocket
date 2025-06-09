<?php
namespace WP_Rocket\Engine\Media\PreloadFonts\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Media\PreloadFonts\Admin\Settings;

/**
 * Preload Fonts admin controller
 *
 * @since 3.19
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 *  Creates an instance of the object.
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Returns an array of events this subscriber listens to
	 *
	 * @return array[]
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_upgrade' => [
				[ 'maybe_enable_auto_preload_fonts', 9, 2 ],
				[ 'maybe_clear_preload_fonts_values', 10, 2 ],
			],
		];
	}

	/**
	 * Enables the auto preload fonts option if the old preload fonts option is not empty.
	 *
	 * This function checks the value of the `rocket_preload_fonts` option.
	 * If it contains a non-empty value, it updates the `auto_preload_fonts` option to `true`.
	 * This is useful for ensuring that automatic font preloading is enabled based on legacy settings.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function maybe_enable_auto_preload_fonts( $new_version, $old_version ): void {
		if ( version_compare( $old_version, '3.19', '>' ) ) {
			return;
		}
		$this->settings->maybe_enable_auto_preload_fonts();
	}

	/**
	 * Removes old preload fonts values when upgrading from versions prior to 3.19.1.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function maybe_clear_preload_fonts_values( string $new_version, string $old_version ): void {
		if ( version_compare( $old_version, '3.19.1', '>' ) ) {
			return;
		}

		$this->settings->maybe_clear_preload_fonts_values();
	}
}
