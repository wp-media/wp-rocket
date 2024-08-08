<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

use WP_Rocket\Engine\Admin\Settings\DataClearingTrait;

class Clean {
	use DataClearingTrait;

	/**
	 * Truncate performance hints tables when clicking on the dashboard button
	 *
	 * @return void
	 */
	public function clean_performance_hints() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_clean_performance_hints' ) ) {
			wp_nonce_ays( '' );
		}

		/**
		 * Filters the value of the Performance hints clean
		 *
		 * @since 3.17
		 *
		 * @param array $clean An array containing the status and message.
		 */
		$clean = apply_filters( 'rocket_performance_hints_clean_all', [] );

		$this->clean_data( $clean, 'rocket_performance_hints_clear_message' );
	}

	public function clean_url_performance_hints() {
		check_admin_referer( 'rocket_clean_performance_hints_url' );

		/**
		 * Fires when cleaning a single URL for the Saas
		 *
		 * @since 3.16
		 */
		do_action( 'rocket_performance_hints_clean_url' );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}
}
