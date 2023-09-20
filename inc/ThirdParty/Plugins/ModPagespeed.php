<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Mod_pagespeed
 *
 * @since  3.8.2
 */
class ModPagespeed implements Subscriber_Interface {

	/**
	 * Subscribed events for Pagespeed module.
	 *
	 * @since  3.8.2
	 * @inheritDoc
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_notices' => 'show_admin_notice',
		];
	}

	/**
	 * Check if mod_pagespeed is enabled on this server with and cache the result on transient.
	 *
	 * @since  3.8.2
	 *
	 * @return bool Status of mod_pagespeed.
	 */
	private function has_pagespeed_with_cache() {
		$has_pagespeed = get_transient( 'rocket_mod_pagespeed_enabled' );

		if ( false === $has_pagespeed ) {
			$has_pagespeed = $this->has_pagespeed();
			set_transient( 'rocket_mod_pagespeed_enabled', (int) $has_pagespeed, DAY_IN_SECONDS );
		}

		return 1 === (int) $has_pagespeed;
	}

	/**
	 * Check if mod_pagespeed is enabled on this server.
	 *
	 * @since  3.8.2
	 *
	 * @return bool
	 */
	private function has_pagespeed(): bool {
		if ( false === strpos( ini_get( 'disable_functions' ), 'apache_get_modules' ) ) {
			$apache_module_loaded = apache_mod_loaded( 'mod_pagespeed', false );

			if ( $apache_module_loaded ) {
				return true;
			}
		}

		$home_request = wp_remote_get(
			home_url(),
			[
				'timeout'   => 3,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$headers = wp_remote_retrieve_headers( $home_request );

		if ( empty( $headers ) ) {
			return false;
		}

		return ( ! empty( $headers['X-Mod-Pagespeed'] ) || ! empty( $headers['X-Page-Speed'] ) );
	}

	/**
	 * Show admin notice message.
	 *
	 * @since  3.8.2
	 */
	public function show_admin_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== get_current_screen()->id ) {
			return;
		}

		if ( ! $this->has_pagespeed_with_cache() ) {
			return;
		}

		$notice_name = 'rocket_error_mod_pagespeed';

		if ( in_array( $notice_name, (array) get_user_meta( get_current_user_id(), 'rocket_boxes', true ), true ) ) {
			return;
		}

		// translators: %1$s is WP Rocket plugin name, %2$s is opening <a> tag, %3$s is closing </a> tag.
		$error_message = sprintf( __( '<strong>%1$s</strong>: Mod PageSpeed is not compatible with this plugin and may cause unexpected results. %2$sMore Info%3$s', 'rocket' ), rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ), '<a target="_blank" href="https://docs.wp-rocket.me/article/1376-mod-pagespeed">', '</a>' );

		rocket_notice_html(
			[
				'status'         => 'error',
				'message'        => $error_message,
				'dismissible'    => '',
				'dismiss_button' => $notice_name,
			]
		);
	}

}
