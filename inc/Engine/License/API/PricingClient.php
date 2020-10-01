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

		set_transient( 'wp_rocket_pricing', $data, 6 * HOUR_IN_SECONDS );

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
		$response = wp_safe_remote_get(
			self::PRICING_ENDPOINT
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return false;
		}

		return json_decode( $body );
	}
}
