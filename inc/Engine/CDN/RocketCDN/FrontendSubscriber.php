<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for RocketCDN frontend integration.
 *
 * Dynamically provides cdn_cnames and cdn_zone values from the RocketCDN subscription data.
 *
 * @since 3.22
 */
class FrontendSubscriber implements Subscriber_Interface {

	/**
	 * CDN context.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Cached RocketCDN URL to avoid multiple transient calls hits per request.
	 *
	 * @var string|null
	 */
	private $rocketcdn_url = null;

	/**
	 * Subscription controller.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor.
	 *
	 * @param Context                $context    CDN context.
	 * @param SubscriptionController $subscription_controller Subscription controller.
	 */
	public function __construct( Context $context, SubscriptionController $subscription_controller ) {
		$this->context                 = $context;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'get_rocket_option_cdn_cnames' => [ 'set_cdn_cnames', 9 ],
			'get_rocket_option_cdn_zone'   => [ 'set_cdn_zone', 9 ],
		];
	}

	/**
	 * Sets the CDN CNAME from the RocketCDN subscription data on the filter level.
	 *
	 * @since 3.22
	 *
	 * @param mixed $cnames The current filter value.
	 *
	 * @return mixed The CDN CNAME array if RocketCDN is active, or the original value for BYOCDN, or empty otherwise.
	 */
	public function set_cdn_cnames( $cnames ) {
		if ( is_admin() ) {
			return $this->handle_admin_cname( $cnames );
		}

		if ( ! $this->context->is_rocketcdn() ) {
			return $cnames;
		}

		$cdn_url = $this->get_rocketcdn_url();

		return empty( $cdn_url ) ? [] : [ $cdn_url ];
	}

	/**
	 * Sets the CDN zone from the RocketCDN subscription data on the filter level.
	 *
	 * @since 3.22
	 *
	 * @param mixed $value The current filter value.
	 *
	 * @return mixed The CDN zone array if RocketCDN is active, or the original value.
	 */
	public function set_cdn_zone( $value ) {
		$cdn_url = $this->get_rocketcdn_url();

		if ( empty( $cdn_url ) ) {
			return $value;
		}

		return [ 'all' ];
	}

	/**
	 * Gets the CDN URL from the RocketCDN subscription data.
	 *
	 * @since 3.22
	 *
	 * @return string The CDN URL if subscription is active, empty string otherwise.
	 */
	private function get_rocketcdn_url(): string {
		// Use memoized value if available.
		if ( null !== $this->rocketcdn_url ) {
			return $this->rocketcdn_url;
		}

		if ( ! $this->context->is_rocketcdn() ) {
			$this->rocketcdn_url = '';
			return '';
		}

		if ( ! $this->subscription_controller->has_active_subscription() ) {
			$this->rocketcdn_url = '';
			return '';
		}

		$this->rocketcdn_url = $this->get_raw_rocketcdn_url();

		return $this->rocketcdn_url;
	}

	/**
	 * Get raw rocketcdn url without any check.
	 *
	 * @return string
	 */
	private function get_raw_rocketcdn_url() {
		return $this->subscription_controller->get_rocketcdn_url();
	}

	/**
	 * Handles the CDN CNAME value in the admin area to ensure that the RocketCDN URL is removed from the list of CNAMEs when the user saves the settings.
	 *
	 * @param array $cnames CNAME array.
	 * @return array
	 */
	private function handle_admin_cname( $cnames ) {
		if ( empty( $cnames ) ) {
			return $cnames;
		}

		return array_filter( $cnames, [ $this, 'filter_rocketcdn_cname' ] );
	}

	/**
	 * Filter rocketcdn cname by comparing the current rocketcdn url with the CNAME value.
	 *
	 * @param string $cname CNAME string.
	 * @return bool
	 */
	private function filter_rocketcdn_cname( $cname ) {
		return $cname !== $this->get_raw_rocketcdn_url();
	}
}
