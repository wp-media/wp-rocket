<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices'         => [
				[ 'maybe_display_purge_notice' ],
				[ 'maybe_display_update_settings_notice' ],
			],
			'rocket_input_sanitize' => [ 'sanitize_options', 20, 2 ],
		];
	}

	/**
	 * This notice is displayed after purging the CloudFlare cache.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function maybe_display_update_settings_notice() {
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
		$pre     = '';
		delete_transient( $user_id . '_cloudflare_update_settings' );

		if ( isset( $notices['pre'] ) ) {
			$pre = $notices['pre'];

			unset( $notices['pre'] );
		}

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
					'message' => $pre . $success,
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

	/**
	 * Sanitize Cloudflare options
	 *
	 * @param array    $input gtArray of sanitized values after being submitted by the form.
	 * @param Settings $settings Settings instance.
	 *
	 * @return array
	 */
	public function sanitize_options( $input, $settings ) {
		$input['do_cloudflare']               = $settings->sanitize_checkbox( $input, 'do_cloudflare' );
		$input['cloudflare_devmode']          = $settings->sanitize_checkbox( $input, 'cloudflare_devmode' );
		$input['cloudflare_auto_settings']    = $settings->sanitize_checkbox( $input, 'cloudflare_auto_settings' );
		$input['cloudflare_protocol_rewrite'] = $settings->sanitize_checkbox( $input, 'cloudflare_protocol_rewrite' );

		$input['cloudflare_email']   = isset( $input['cloudflare_email'] ) ? sanitize_email( $input['cloudflare_email'] ) : '';
		$input['cloudflare_zone_id'] = isset( $input['cloudflare_zone_id'] ) ? sanitize_text_field( $input['cloudflare_zone_id'] ) : '';

		$input['cloudflare_api_key'] = isset( $input['cloudflare_api_key'] ) ? sanitize_text_field( $input['cloudflare_api_key'] ) : '';

		if ( defined( 'WP_ROCKET_CF_API_KEY' ) ) {
			$input['cloudflare_api_key'] = rocket_get_constant( 'WP_ROCKET_CF_API_KEY', '' );
		}

		return $input;
	}
}
