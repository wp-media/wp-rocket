<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Settings instance.
	 *
	 * @var Settings
	 */
	protected $settings;

	/**
	 * Creates an instance of the class.

	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                 => [
				[ 'maybe_display_preload_notice' ],
			],
			'rocket_options_changed'        => 'preload_homepage',
			'switch_theme'                  => 'preload_homepage',
			'rocket_after_clean_used_css'   => 'preload_homepage',
			'rocket_domain_options_changed' => 'clear_and_preload',
			'rocket_input_sanitize'         => 'sanitize_options',
			'wp_rocket_upgrade'             => [ 'maybe_clean_cron', 15, 2 ],
		];
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		$this->settings->maybe_display_preload_notice();
	}

	/**
	 * Preload the homepage
	 *
	 * @return void
	 */
	public function preload_homepage() {
		$this->settings->preload_homepage();
	}

	/**
	 * Clear the cache table and preload
	 *
	 * @return void
	 */
	public function clear_and_preload() {
		$this->settings->clear_and_preload();
	}

	/**
	 * Sanitizes Preload Excluded URI option when saving the settings
	 *
	 * @param array $input Array of values submitted from the form.
	 *
	 * @return array
	 */
	public function sanitize_options( $input ): array {
		if ( empty( $input['preload_excluded_uri'] ) ) {
			$input['preload_excluded_uri'] = [];

			return $input;
		}

		$input['preload_excluded_uri'] = rocket_sanitize_textarea_field( 'preload_excluded_uri', $input['preload_excluded_uri'] );

		return $input;
	}

	/**
	 * Unlock all preload URL on update.
	 *
	 * @param string $wp_rocket_version Latest WP Rocket version.
	 * @param string $actual_version Installed WP Rocket version.
	 */
	public function maybe_clean_cron( $wp_rocket_version, $actual_version ) {
		if ( version_compare( $actual_version, '3.12.5', '<' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'rocket_preload_revert_old_in_progress_rows' );
	}
}
