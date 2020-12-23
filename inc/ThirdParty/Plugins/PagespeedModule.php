<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Smush
 *
 * @since  3.8
 * @author Soponar Cristina
 */
class PagespeedModule implements Subscriber_Interface {

	/**
	 * Subscribed events for Pagespeed module.
	 *
	 * @since  3.8
	 * @inheritDoc
	 */
	public static function get_subscribed_events(): array {
		$has_pagespeed = get_transient( 'rocket_mod_pagespeed_enabled' );

		if ( false === $has_pagespeed ) {
			$has_pagespeed = self::has_pagespeed();
			set_transient( 'rocket_mod_pagespeed_enabled', (int) $has_pagespeed, DAY_IN_SECONDS );
		}

		if ( 1 !== (int) $has_pagespeed ) {
			return [];
		}

		return [
			'admin_notices' => 'show_admin_notice'
		];
	}

	private static function has_pagespeed (): bool {
		global $is_apache;

		if ( $is_apache && function_exists( "apache_get_modules" ) ) {
			if ( in_array("mod_pagespeed", apache_get_modules(), true ) ) {
				return true;
			}
		}

		if ( empty( $headers = get_headers(home_url() ?? "/", true ) ) ) {
			return false;
		}

		if ( ! empty( $headers["X-Mod-Pagespeed"] ) || ! empty( $headers["X-Page-Speed"] ) ) {
			return true;
		}

		return false;
	}

	public function show_admin_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		// translators: %s is WP Rocket plugin name.
		$warning = '<p>' . sprintf( __( '<strong>%s</strong>: The following modules are not compatible with this plugin and may cause unexpected results:', 'rocket' ), rocket_get_constant( 'WP_ROCKET_PLUGIN_NAME' ) ) . '</p>';

		rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $warning,
			]
		);
	}

}
