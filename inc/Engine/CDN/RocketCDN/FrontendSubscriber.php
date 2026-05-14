<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Admin\Options_Data;
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
	 * WP Rocket Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * RocketCDN API Client instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options    WP Rocket Options_Data instance.
	 * @param APIClient    $api_client RocketCDN API Client instance.
	 */
	public function __construct( Options_Data $options, APIClient $api_client ) {
		$this->options    = $options;
		$this->api_client = $api_client;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'pre_get_rocket_option_cdn_cnames' => [ 'set_cdn_cnames', 9 ],
			'pre_get_rocket_option_cdn_zone'   => [ 'set_cdn_zone', 9 ],
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
	 * Gets the CDN URL from the RocketCDN subscription data.
	 *
	 * @since 3.22
	 *
	 * @return string The CDN URL if subscription is active, empty string otherwise.
	 */
	private function get_rocketcdn_url(): string {
		if ( 'rocketcdn' !== $this->options->get( 'cdn_type' ) ) {
			return '';
		}

		$subscription = $this->api_client->get_subscription_data();

		if ( 'running' !== $subscription['subscription_status'] ) {
			return '';
		}

		return $subscription['cdn_url'];
	}
}
