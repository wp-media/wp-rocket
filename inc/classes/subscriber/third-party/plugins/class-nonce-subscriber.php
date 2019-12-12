<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for nonce validation with WooCommerce.
 *
 * @since  3.5.1
 * @author Soponar Cristina
 */
class Nonce_Subscriber implements Subscriber_Interface {
	/**
	 * Subscribed events for nonces.
	 *
	 * @since  3.5.1
	 * @author Soponar Cristina
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return [];
		}
		return [
			'nonce_user_logged_out' => [ 'maybe_revert_uid_for_nonce_actions', PHP_INT_MAX, 2 ],
		];
	}

	/**
	 * Set $uid to 0 for certain nonce actions.
	 *
	 * @since  3.5.1
	 * @access public
	 * @author Soponar Cristina
	 *
	 * @param int    $uid    ID of the nonce-owning user.
	 * @param string $action The nonce action.
	 *
	 * @return int $uid      ID of the nonce-owning user.
	 */
	public function maybe_revert_uid_for_nonce_actions( $uid, $action ) {
		if ( $uid && 0 !== $uid && $action && in_array( $action, $this->get_nonce_actions(), true ) ) {
			$uid = 0;
		}
		return $uid;
	}

	/**
	 * List with nonce actions which needs to revert the $uid.
	 *
	 * @since  3.5.1
	 * @access private
	 * @author Soponar Cristina
	 *
	 * @return array $nonce_actions List with all nonce actions.
	 */
	private function get_nonce_actions() {
		$nonce_actions = [
			'wcmd-subscribe-secret', // WooCommerce MailChimp Discount.
			'td-block', // "Load more" AJAX functionality of the Newspaper theme.
		];
		return $nonce_actions;
	}
}
