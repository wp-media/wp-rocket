<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'                             => [
				[ 'maybe_display_purge_notice' ],
				[ 'maybe_print_update_settings_notice' ],
			],
		];
	}

	/**
	 * This notice is displayed after purging the CloudFlare cache.
	 */
	public function maybe_display_purge_notice() {
		if ( ! current_user_can( 'rocket_purge_cloudflare_cache' ) ) {
			return;
		}

		$user_id = get_current_user_id();
		$notice  = get_transient( $user_id . '_cloudflare_purge_result' );

		if ( ! $notice ) {
			return;
		}

		delete_transient( $user_id . '_cloudflare_purge_result' );

		rocket_notice_html(
			[
				'status'  => $notice['result'],
				'message' => $notice['message'],
			]
		);
	}

	/**
	 * This notice is displayed after modifying the CloudFlare settings.
	 */
	public function maybe_print_update_settings_notice() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) || 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$user_id = get_current_user_id();
		$notices = get_transient( $user_id . '_cloudflare_update_settings' );
		if ( ! $notices ) {
			return;
		}

		$errors  = '';
		$success = '';
		delete_transient( $user_id . '_cloudflare_update_settings' );
		foreach ( $notices as $notice ) {
			if ( 'error' === $notice['result'] ) {
				$errors .= $notice['message'] . '<br>';
			} elseif ( 'success' === $notice['result'] ) {
				$success .= $notice['message'] . '<br>';
			}
		}

		if ( ! empty( $success ) ) {
			rocket_notice_html(
				[
					'message' => $success,
				]
			);
		}

		if ( ! empty( $errors ) ) {
			rocket_notice_html(
				[
					'status'  => 'error',
					'message' => $errors,
				]
			);
		}
	}
}
