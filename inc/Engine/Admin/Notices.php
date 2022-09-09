<?php

namespace WP_Rocket\Engine\Admin;

class Notices {

	/**
	 * Dispaly notice for missing or incorrect action scheduler tables.
	 *
	 * @return void
	 */
	public function maybe_display_as_missed_tables_notice() {

		if ( function_exists( 'get_current_screen' ) && 'tools_page_action-scheduler' === get_current_screen()->id ) {
			return;
		}

		// Bail out if tables are correct.
		if ( $this->is_valid_as_table() ) {
			return;
		}

		$as_tools_link = menu_page_url( 'action-scheduler', false );
			$message   = sprintf(
			// translators: %1$s = plugin name, %2$s = opening anchor tag, %3$s = closing anchor tag.
				__( '%1$s: We detected missing database table related to Action Scheduler. Please visit the following %2$sURL%3$s to recreate it, as it is needed for WP Rocket to work correctly.', 'rocket' ),
				'<strong>WP Rocket</strong>',
				'<a href="' . $as_tools_link . '">',
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
	 * Check if Action Scheduler tables are missing or incorrect.
	 *
	 * @return boolean
	 */
	private function is_valid_as_table() {
		$cached_count = get_transient( 'rocket_rucss_as_tables_count' );
		if ( false !== $cached_count && ! is_admin() ) { // Stop caching in admin UI.
			return 4 === (int) $cached_count;
		}

		global $wpdb;

		$exp = "'^" . $wpdb->prefix . "actionscheduler_(logs|actions|groups|claims)$'";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_as_tables = $wpdb->get_col(
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare( 'SHOW TABLES FROM ' . DB_NAME . ' WHERE Tables_in_' . DB_NAME . ' LIKE %s AND Tables_in_' . DB_NAME . ' REGEXP ' . $exp, '%actionscheduler%' )
		);

		set_transient( 'rocket_rucss_as_tables_count', count( $found_as_tables ), rocket_get_constant( 'DAY_IN_SECONDS', 24 * 60 * 60 ) );
		return 4 === count( $found_as_tables );
	}
}
