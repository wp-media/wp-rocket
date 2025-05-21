<?php
namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Preconnect External Domains admin controller
 *
 * @since 3.19
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Controller instance
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
			'update_option_wp_rocket_settings' => [ 'maybe_clear_preconnect_domains', 12, 2 ],
		];
	}

	/**
	 * Clears the preconnect domains table if relevant settings are changed.
	 *
	 * @param array $old_settings Old settings.
	 * @param array $new_settings New settings.
	 * @return void
	 */
	public function maybe_clear_preconnect_domains( array $old_settings, array $new_settings ): void {
		$this->settings->maybe_clear_preconnect_external_domains( $old_settings, $new_settings );
	}
}
