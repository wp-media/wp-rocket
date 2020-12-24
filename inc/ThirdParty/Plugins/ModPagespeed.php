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
		if ( ! self::has_pagespeed_with_cache() ) {
			return [];
		}

		return [
			'admin_notices' => 'show_admin_notice',
		];
	}

	/**
	 * Check if mod_pagespeed is enabled on this server with and cache the result on transient.
	 *
	 * @return bool Status of mod_pagespeed.
	 */
	private static function has_pagespeed_with_cache() {
		$has_pagespeed = get_transient( 'rocket_mod_pagespeed_enabled' );

		if ( false === $has_pagespeed ) {
			$has_pagespeed = self::has_pagespeed();
			set_transient( 'rocket_mod_pagespeed_enabled', (int) $has_pagespeed, DAY_IN_SECONDS );
		}

		return 1 === (int) $has_pagespeed;
	}

	/**
	 * Check if mod_pagespeed is enabled on this server.
	 *
	 * @return bool
	 */
	private static function has_pagespeed(): bool {
		global $is_apache;

		if ( $is_apache && function_exists( 'apache_get_modules' ) ) {
			if ( in_array( 'mod_pagespeed', apache_get_modules(), true ) ) {
				return true;
			}
		}

		$headers = get_headers( home_url() ?? '/', true );

		if ( empty( $headers ) ) {
			return false;
		}

		if ( ! empty( $headers['X-Mod-Pagespeed'] ) || ! empty( $headers['X-Page-Speed'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Show admin notice message.
	 */
	public function show_admin_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// translators: %1$s is WP Rocket plugin name, %2$s is opening <a> tag, %3$s is closing </a> tag.
		$warning = '<p>' . sprintf( __( '<strong>%1$s</strong>: Mod PageSpeed is not compatible with this plugin and may cause unexpected results. %2$sMore Info%3$s', 'rocket' ), rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ), '<a href="https://docs.wp-rocket.me/article/670-hosting-compatibility">', '</a>' ) . '</p>';

		rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $warning,
			]
		);
	}

}
