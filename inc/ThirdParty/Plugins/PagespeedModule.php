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
		if ( ! self::has_pagespeed() ) {
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
			return false;
		}

		if ( ! empty( $headers = get_headers(home_url() ?? null ) ) ) {
			if ( ! empty( $headers["X-Mod-Pagespeed"] ) || ! empty( $headers["X-Page-Speed"] ) ) {
				return true;
			}
		}

		return false;
	}

	public function show_admin_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$message = "";

		rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $message,
			]
		);
	}

}
