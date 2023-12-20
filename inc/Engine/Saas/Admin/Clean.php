<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Saas\Admin;

use WP_Rocket\Admin\Options_Data;

class Clean {
	/**
	 * Truncate SaaS tables when clicking on the dashboard button
	 *
	 * @return void
	 */
	public function clean_saas() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_clean_saas' ) ) {
			wp_nonce_ays( '' );
		}

		/**
		 * Filters the value of the SaaS clean
		 *
		 * @since 3.16
		 *
		 * @param array An array containing the status and message.
		 */
		$clean = apply_filters( 'rocket_saas_clean_all', [] );

		if (
			empty( $clean )
			||
			'die' === $clean['status']
		) {
			wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		if ( 'error' === $clean['status'] ) {
			wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		rocket_clean_domain();

		rocket_dismiss_box( 'rocket_warning_plugin_modification' );

		set_transient(
			'rocket_saas_clean_message',
			$clean
		);

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Clean SaaS for the current URL.
	 *
	 * @return void
	 */
	public function clean_url_saas() {
		check_admin_referer( 'rocket_clean_saas_url' );

		/**
		 * Fires when cleaning a single URL for the Saas
		 *
		 * @since 3.16
		 */
		do_action( 'rocket_saas_clean_url' );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}
}
