<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * The tracking service.
	 *
	 * @var Tracking
	 */
	private $tracking;

	/**
	 * Constructor.
	 *
	 * @param Tracking $tracking The tracking service.
	 */
	public function __construct( Tracking $tracking ) {
		$this->tracking = $tracking;
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'update_option_wp_rocket_settings'    => [ 'track_option_change', 10, 2 ],
			'wp_rocket_upgrade'                   => [ 'migrate_optin', 10, 2 ],
			'rocket_dashboard_after_account_data' => [ 'render_optin', 9 ],
			'wp_ajax_rocket_toggle_optin'         => [ 'ajax_toggle_optin' ],
			'admin_enqueue_scripts'               => [ 'localize_optin_status', 15 ],
			'admin_print_scripts'                 => [ 'inject_mixpanel_script' ],
			'rocket_mixpanel_optin_changed'       => 'track_optin_change',
		];
	}

	/**
	 * Track option change.
	 *
	 * @param mixed $old_value The old value of the option.
	 * @param mixed $value     The new value of the option.
	 *
	 * @return void
	 */
	public function track_option_change( $old_value, $value ): void {
		$this->tracking->track_option_change( $old_value, $value );
	}

	/**
	 * Migrate opt-in to new package on upgrade
	 *
	 * @param string $new_version The new version of the plugin.
	 * @param string $old_version The old version of the plugin.
	 *
	 * @return void
	 */
	public function migrate_optin( $new_version, $old_version ): void {
		$this->tracking->migrate_optin( $new_version, $old_version );
	}

	/**
	 * Render the opt-in section.
	 *
	 * @return void
	 */
	public function render_optin(): void {
		$this->tracking->render_optin();
	}

	/**
	 * Handle AJAX request to toggle opt-in.
	 *
	 * @return void
	 */
	public function ajax_toggle_optin(): void {
		$this->tracking->ajax_toggle_optin();
	}

	/**
	 * Localize opt-in status to JavaScript.
	 *
	 * @return void
	 */
	public function localize_optin_status(): void {
		$this->tracking->localize_optin_status();
	}

	/**
	 * Inject Mixpanel JavaScript SDK.
	 *
	 * @since 3.19.2
	 * @return void
	 */
	public function inject_mixpanel_script(): void {
		$this->tracking->inject_mixpanel_script();
	}

	/**
	 * Track opt-in change event.
	 *
	 * @param bool $status The new opt-in status.
	 *
	 * @return void
	 */
	public function track_optin_change( $status ): void {
		$this->tracking->track_optin_change( $status );
	}
}
