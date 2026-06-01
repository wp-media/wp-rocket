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
			'pre_get_rocket_option_cdn_cnames' => [ 'set_cdn_cnames', 9 ],
			'pre_get_rocket_option_cdn_zone'   => [ 'set_cdn_zone', 9 ],
			'rocket_field_cdn_cnames'          => 'remove_rocketcdn_cnames',
		];
	}

	/**
	 * Sets the CDN CNAME from the RocketCDN subscription data on the filter level.
	 *
	 * @since 3.22
	 *
	 * @param mixed $value The current pre-filter value.
	 *
	 * @return mixed The CDN CNAME array if RocketCDN is active, or the original value.
	 */
	public function set_cdn_cnames( $value ) {
		$cdn_url = $this->get_rocketcdn_url();

		if ( empty( $cdn_url ) ) {
			return $value;
		}

		return [ $cdn_url ];
	}

	/**
	 * Sets the CDN zone from the RocketCDN subscription data on the filter level.
	 *
	 * @since 3.22
	 *
	 * @param mixed $value The current pre-filter value.
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
	 * Remove RocketCDN URL from the list of CNAMEs in 'Other CDN' settings field.
	 *
	 * Filters out the RocketCDN URL from the provided list of CNAMEs
	 * to prevent it from being displayed in 'Other CDN' settings field.
	 *
	 * @param array $cnames List of CNAME URLs to filter.
	 * @return array Filtered array of CNAMEs with RocketCDN URL removed.
	 */
	public function remove_rocketcdn_cnames( $cnames ) {
		// Remove RocketCDN URL from the list of CNAMEs to display in the 'Other CDN' settings field.
		$rocketcdn_url = $this->subscription_controller->get_rocketcdn_url();

		if ( empty( $rocketcdn_url ) ) {
			return $cnames;
		}

		return array_diff( $cnames, [ $rocketcdn_url ] );
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

		$this->rocketcdn_url = $this->subscription_controller->get_rocketcdn_url();

		return $this->rocketcdn_url;
	}
}
