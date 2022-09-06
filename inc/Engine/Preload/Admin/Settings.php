<?php

namespace WP_Rocket\Engine\Preload\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Instance of options handler.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Maybe display the preload notice.
	 *
	 * @return void
	 */
	public function maybe_display_preload_notice() {
		if ( ! $this->can_display_notice() ) {
			return;
		}

		if ( false === get_transient( 'wpr_preload_running' ) ) {
			return;
		}

		$message = sprintf(
			// translators: %1$s = plugin name.
			__( '%1$s: The preload service is now active. After the initial preload it will continue to cache all your pages whenever they are purged. No further action is needed.', 'rocket' ),
			'<strong>WP Rocket</strong>'
		);

		rocket_notice_html(
			[
				'status'  => 'info',
				'message' => $message,
				'id'      => 'rocket-notice-preload-processing',
			]
		);
	}

	/**
	 * Checks if we can display the Preload notices.
	 *
	 * @return bool
	 */
	private function can_display_notice(): bool {
		$screen = get_current_screen();

		if (
			isset( $screen->id )
			&&
			'settings_page_wprocket' !== $screen->id
		) {
			return false;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return false;
		}

		return $this->is_enabled();
	}

	/**
	 * Display missing as table notice if they are not present.
	 *
	 * @return void
	 */
	public function maybe_display_as_missed_tables_notice() {

		if ( function_exists( 'get_current_screen' ) && 'tools_page_action-scheduler' === get_current_screen()->id ) {
			return;
		}

		if ( $this->is_valid_as_tables() ) {
			return;
		}

		$message = sprintf(
		// translators: %1$s = plugin name, %2$s = opening anchor tag, %3$s = closing anchor tag.
			__( '%1$s: We detected missing database table related to Action Scheduler. Please visit the following %2$sURL%3$s to recreate it, as it is needed for WP Rocket to work correctly.', 'rocket' ),
			'<strong>WP Rocket</strong>',
			'<a href="' . menu_page_url( 'action-scheduler', false ) . '">',
			'</a>'
		);
		rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $message,
				'id'      => 'rocket-notice-as-missed-tables',
			]
		);
	}

	/**
	 * Determines if Preload option is enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'manual_preload', 0 );
	}

	/**
	 * Checks if Action scheduler tables are there or not.
	 *
	 * @since 3.11.0.3
	 *
	 * @return bool
	 */
	private function is_valid_as_tables() {
		$cached_count = get_transient( 'rocket_preload_as_tables_count' );
		if ( false !== $cached_count && ! is_admin() ) { // Stop caching in admin UI.
			return 4 === (int) $cached_count;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_as_tables = $wpdb->get_col(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'actionscheduler%' )
		);

		set_transient( 'rocket_preload_as_tables_count', count( $found_as_tables ), rocket_get_constant( 'DAY_IN_SECONDS', 24 * 60 * 60 ) );

		return 4 === count( $found_as_tables );
	}
}
