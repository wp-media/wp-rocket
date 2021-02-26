<?php

namespace WP_Rocket\Engine\License\API;

class PricingClient {
	const PRICING_ENDPOINT = 'https://wp-rocket.me/stat/1.0/wp-rocket/pricing.php';

	/**
	 * Gets pricing data from cache if it exists, else gets it from the pricing endpoint
	 *
	 * Cache the pricing data for 6 hours in a transient
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	public function get_pricing_data() {
		$cached_data = get_transient( 'wp_rocket_pricing' );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$data = $this->get_raw_pricing_data();

		if ( false === $data ) {
			return false;
		}

		set_transient( 'wp_rocket_pricing', $data, 12 * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets the pricing data from the pricing endpoint
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	private function get_raw_pricing_data() {
		if ( (bool) get_transient( 'wp_rocket_pricing_timeout_active' ) ) {
			return false;
		}

		$response = wp_safe_remote_get(
			self::PRICING_ENDPOINT
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->set_timeout_transients();

			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			$this->set_timeout_transients();

			return false;
		}

		delete_transient( 'wp_rocket_pricing_timeout' );
		delete_transient( 'wp_rocket_pricing_timeout_active' );

		return json_decode( $body );
	}

	/**
	 * Set pricing timeout transients.
	 *
	 * @since 3.8.4
	 *
	 * @return void
	 */
	private function set_timeout_transients() {
		$timeout = (int) get_transient( 'wp_rocket_pricing_timeout' );
		$timeout = ( 0 === $timeout )
			? 300
			: ( 2 * $timeout <= DAY_IN_SECONDS
				? 2 * $timeout :
				DAY_IN_SECONDS
			);

		set_transient( 'wp_rocket_pricing_timeout', $timeout, WEEK_IN_SECONDS );
		set_transient( 'wp_rocket_pricing_timeout_active', true, $timeout );
	}
}
