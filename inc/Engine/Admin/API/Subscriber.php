<?php

namespace WP_Rocket\Engine\Admin\API;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init'         => 'register_route',
			'admin_enqueue_scripts' => [ 'enqueue_url', 999 ],
		];
	}


	/**
	 * Enqueue the URL for option exporting.
	 *
	 * @return void
	 */
	public function enqueue_url() {
		wp_localize_script(
			'wpr-admin-common',
			'rocket_option_export',
			[
				'rest_url_option_export' => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_export' ), 'rocket_export' ),
			]
		);
	}

	/**
	 * Register REST route.
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'options/export',
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'export_options' ],
				'permission_callback' => [ $this, 'has_permissions' ],
			]
			);
	}

	/**
	 * Export options.
	 *
	 * @return void
	 */
	public function export_options() {
		list( $filename, $options ) = rocket_export_options();

		nocache_headers();
		@header( 'Content-Type: application/json' ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@header( 'Content-Disposition: attachment; filename="' . $filename . '"' ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@header( 'Content-Transfer-Encoding: binary' ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@header( 'Content-Length: ' . strlen( $options ) ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@header( 'Connection: close' ); //phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		echo $options; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit();
	}

	/**
	 * Has permission to use the API route.
	 *
	 * @return bool
	 */
	public function has_permissions() {
		return current_user_can( 'rocket_manage_options' );
	}
}
