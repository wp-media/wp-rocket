<?php

namespace WP_Rocket\Engine\Admin\Deactivation;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * DeactivationIntent instance
	 *
	 * @var DeactivationIntent
	 */
	private $deactivation;

	/**
	 * Constructor
	 *
	 * @param DeactivationIntent $deactivation DeactivationIntent instance.
	 */
	public function __construct( DeactivationIntent $deactivation ) {
		$this->deactivation = $deactivation;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_footer-plugins.php'                    => 'insert_deactivation_intent_form',
			'admin_enqueue_scripts'                       => 'add_modal_assets',
			'admin_post_rocket_deactivation'              => 'safe_mode_or_deactivate',
			'plugin_action_links_wp-rocket/wp-rocket.php' => 'add_data_attribute',
		];
	}

	/**
	 * Inserts the deactivation intent form on plugins page
	 *
	 * @since 3.11.1
	 *
	 * @return void
	 */
	public function insert_deactivation_intent_form() {
		$this->deactivation->insert_deactivation_intent_form();
	}

	/**
	 * Add modal assets on the plugins page
	 *
	 * @since 3.11.1
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function add_modal_assets( $hook ) {
		$this->deactivation->add_modal_assets( $hook );
	}

	/**
	 * Apply safe mode or deactivate the plugin
	 *
	 * @since 3.11.1
	 *
	 * @return void
	 */
	public function safe_mode_or_deactivate() {
		check_admin_referer( 'rocket_deactivation', '_wpnonce' );

		$referer = wp_get_referer();

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( $referer );
			exit;
		}

		$mode = isset( $_POST['mode'] ) ? sanitize_key( $_POST['mode'] ) : '';

		if ( 'safe_mode' === $mode ) {
				$this->deactivation->activate_safe_mode();
		} elseif ( 'deactivate' === $mode ) {
			$snooze = isset( $_POST['snooze'] ) ? absint( $_POST['snooze'] ) : 0;

			$this->deactivation->deactivate_and_snooze( $snooze );

			$referer = add_query_arg( 'deactivate', '1', $referer );
		}

		wp_safe_redirect( $referer );
		exit;
	}

	/**
	 * Add data attribute to WP Rocket deactivation link for the modal
	 *
	 * @since 3.11.1
	 *
	 * @param string[] $actions An array of plugin action links.
	 *
	 * @return array
	 */
	public function add_data_attribute( $actions ): array {
		return $this->deactivation->add_data_attribute( $actions );
	}
}
