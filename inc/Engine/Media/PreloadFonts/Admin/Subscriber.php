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
	 * Controller instance
	 *
	 * @var Settings
	 */
	private $controller;

	/**
	 *  Creates an instance of the object.
	 *
	 * @param Settings $controller Controller instance.
	 */
	public function __construct( Settings $controller ) {
		$this->controller = $controller;
	}

	/**
	 * Returns an array of events this subscriber listens to
	 *
	 * @return array[]
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_upgrade' => [ 'maybe_enable_auto_preload_fonts', 9, 2 ],
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
		$this->controller->maybe_enable_auto_preload_fonts();
	}
}
