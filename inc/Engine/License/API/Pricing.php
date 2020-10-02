<?php

namespace WP_Rocket\Engine\License\API;

class Pricing {
	/**
	 * The pricing data object
	 *
	 * @var object
	 */
	private $pricing;

	/**
	 * Instantiate the class
	 *
	 * @param object $pricing The pricing object.
	 */
	public function __construct( $pricing ) {
		$this->pricing = $pricing;
	}

	/**
	 * Gets the single license pricing data
	 *
	 * @return null|object
	 */
	public function get_single_pricing() {
		return isset( $this->pricing->licenses->single ) ? $this->pricing->licenses->single : null;
	}

	/**
	 * Gets the plus license pricing data
	 *
	 * @return null|object
	 */
	public function get_plus_pricing() {
		return isset( $this->pricing->licenses->plus ) ? $this->pricing->licenses->plus : null;
	}

	/**
	 * Gets the infinite license pricing data
	 *
	 * @return null|object
	 */
	public function get_infinite_pricing() {
		return isset( $this->pricing->licenses->infinite ) ? $this->pricing->licenses->infinite : null;
	}

	/**
	 * Gets the license renewal pricing data
	 *
	 * @return null|object
	 */
	public function get_renewals_data() {
		return isset( $this->pricing->renewals ) ? $this->pricing->renewals : null;
	}

	/**
	 * Gets the promotion data
	 *
	 * @return null|object
	 */
	public function get_promo_data() {
		return isset( $this->pricing->promo ) ? $this->pricing->promo : null;
	}

	/**
	 * Checks if a promotion is currently active
	 *
	 * @return boolean
	 */
	public function is_promo_active() {
		$promo_data = $this->get_promo_data();

		if ( is_null( $promo_data ) ) {
			return false;
		}

		if ( ! isset( $promo_data->start_date, $promo_data->end_date ) ) {
			return false;
		}

		$current_time = time();

		return (
			$promo_data->start_date < $current_time
			&&
			$promo_data->end_date > $current_time
		);
	}

	/**
	 * Gets promotion end date
	 *
	 * @return int
	 */
	public function get_promo_end() {
		return isset( $this->get_promo_data()->end_date ) ? $this->get_promo_data()->end_date : 0;
	}

	/**
	 * Gets the regular upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_regular_single_to_plus_price() {
		return isset( $this->get_plus_pricing()->prices->from_single->regular )
		? $this->get_plus_pricing()->prices->from_single->regular
		: 0;
	}

	/**
	 * Gets the current upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_single_to_plus_price() {
		$sale_price = isset( $this->get_plus_pricing()->prices->from_single->sale )
		? $this->get_plus_pricing()->prices->from_single->sale
		: 0;

		return $this->is_promo_active() ? $sale_price : $this->get_regular_single_to_plus_price();
	}

	/**
	 * Gets the regular upgrade price from single to infinite
	 *
	 * @return int
	 */
	public function get_regular_single_to_infinite_price() {
		return isset( $this->get_infinite_pricing()->prices->from_single->regular )
		? $this->get_infinite_pricing()->prices->from_single->regular
		: 0;
	}

	/**
	 * Gets the current upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_single_to_infinite_price() {
		$sale_price = isset( $this->get_infinite_pricing()->prices->from_single->sale )
		? $this->get_infinite_pricing()->prices->from_single->sale
		: 0;

		return $this->is_promo_active() ? $sale_price : $this->get_regular_single_to_infinite_price();
	}

	/**
	 * Gets the regular upgrade price from plus to infinite
	 *
	 * @return int
	 */
	public function get_regular_plus_to_infinite_price() {
		return isset( $this->get_infinite_pricing()->prices->from_plus->regular )
		? $this->get_infinite_pricing()->prices->from_plus->regular
		: 0;
	}

	/**
	 * Gets the current upgrade price from plus to infinite
	 *
	 * @return int
	 */
	public function get_plus_to_infinite_price() {
		$sale_price = isset( $this->get_infinite_pricing()->prices->from_plus->sale )
		? $this->get_infinite_pricing()->prices->from_plus->sale
		: 0;

		return $this->is_promo_active() ? $sale_price : $this->get_regular_plus_to_infinite_price();
	}
}
