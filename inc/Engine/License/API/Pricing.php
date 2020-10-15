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
		if (
			! isset( $this->pricing->licenses->single )
			||
			! is_object( $this->pricing->licenses->single )
		) {
			return null;
		}

		return $this->pricing->licenses->single;
	}

	/**
	 * Gets the plus license pricing data
	 *
	 * @return null|object
	 */
	public function get_plus_pricing() {
		if (
			! isset( $this->pricing->licenses->plus )
			||
			! is_object( $this->pricing->licenses->plus )
		) {
			return null;
		}

		return $this->pricing->licenses->plus;
	}

	/**
	 * Gets the infinite license pricing data
	 *
	 * @return null|object
	 */
	public function get_infinite_pricing() {
		if (
			! isset( $this->pricing->licenses->infinite )
			||
			! is_object( $this->pricing->licenses->infinite )
		) {
			return null;
		}

		return $this->pricing->licenses->infinite;
	}

	/**
	 * Gets the license renewal pricing data
	 *
	 * @return null|object
	 */
	public function get_renewals_data() {
		if (
			! isset( $this->pricing->renewals )
			||
			! is_object( $this->pricing->renewals )
		) {
			return null;
		}

		return $this->pricing->renewals;
	}

	/**
	 * Gets the promotion data
	 *
	 * @return null|object
	 */
	public function get_promo_data() {
		if (
			! isset( $this->pricing->promo )
			||
			! is_object( $this->pricing->promo )
		) {
			return null;
		}

		return $this->pricing->promo;
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
			absint( $promo_data->start_date ) < $current_time
			&&
			absint( $promo_data->end_date ) > $current_time
		);
	}

	/**
	 * Gets promotion end date
	 *
	 * @return int
	 */
	public function get_promo_end() {
		$promo = $this->get_promo_data();

		if (
			is_null( $promo )
			||
			! isset( $promo->end_date )
		) {
			return 0;
		}

		return absint( $promo->end_date );
	}

	/**
	 * Gets the regular upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_regular_single_to_plus_price() {
		$plus_pricing = $this->get_plus_pricing();

		if (
			is_null( $plus_pricing )
			||
			! isset( $plus_pricing->prices->from_single->regular )
		) {
			return 0;
		}

		return $plus_pricing->prices->from_single->regular;
	}

	/**
	 * Gets the current upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_single_to_plus_price() {
		$plus_pricing = $this->get_plus_pricing();
		$regular      = $this->get_regular_single_to_plus_price();

		if (
			is_null( $plus_pricing )
			||
			! isset( $plus_pricing->prices->from_single->sale )
		) {
			return $regular;
		}

		return $this->is_promo_active() ? $plus_pricing->prices->from_single->sale : $regular;
	}

	/**
	 * Gets the regular upgrade price from single to infinite
	 *
	 * @return int
	 */
	public function get_regular_single_to_infinite_price() {
		$infinite_pricing = $this->get_infinite_pricing();

		if (
			is_null( $infinite_pricing )
			||
			! isset( $infinite_pricing->prices->from_single->regular )
		) {
			return 0;
		}

		return $infinite_pricing->prices->from_single->regular;
	}

	/**
	 * Gets the current upgrade price from single to plus
	 *
	 * @return int
	 */
	public function get_single_to_infinite_price() {
		$infinite_pricing = $this->get_infinite_pricing();
		$regular          = $this->get_regular_single_to_infinite_price();

		if (
			is_null( $infinite_pricing )
			||
			! isset( $infinite_pricing->prices->from_single->sale )
		) {
			return $regular;
		}

		return $this->is_promo_active() ? $infinite_pricing->prices->from_single->sale : $regular;
	}

	/**
	 * Gets the regular upgrade price from plus to infinite
	 *
	 * @return int
	 */
	public function get_regular_plus_to_infinite_price() {
		$infinite_pricing = $this->get_infinite_pricing();

		if (
			is_null( $infinite_pricing )
			||
			! isset( $infinite_pricing->prices->from_plus->regular )
		) {
			return 0;
		}

		return $infinite_pricing->prices->from_plus->regular;
	}

	/**
	 * Gets the current upgrade price from plus to infinite
	 *
	 * @return int
	 */
	public function get_plus_to_infinite_price() {
		$infinite_pricing = $this->get_infinite_pricing();
		$regular          = $this->get_regular_plus_to_infinite_price();

		if (
			is_null( $infinite_pricing )
			||
			! isset( $infinite_pricing->prices->from_plus->sale )
		) {
			return $regular;
		}

		return $this->is_promo_active() ? $infinite_pricing->prices->from_plus->sale : $regular;
	}

	/**
	 * Gets the number of websites allowed for the single license
	 *
	 * @return int
	 */
	public function get_single_websites_count() {
		$single_pricing = $this->get_single_pricing();

		if (
			is_null( $single_pricing )
			||
			! isset( $single_pricing->websites )
		) {
			return 0;
		}

		return (int) $single_pricing->websites;
	}

	/**
	 * Gets the number of websites allowed for the plus license
	 *
	 * @return int
	 */
	public function get_plus_websites_count() {
		$plus_pricing = $this->get_plus_pricing();

		if (
			is_null( $plus_pricing )
			||
			! isset( $plus_pricing->websites )
		) {
			return 0;
		}

		return (int) $plus_pricing->websites;
	}

	/**
	 * Gets the number of websites allowed for the infinite license
	 *
	 * @return int
	 */
	public function get_infinite_websites_count() {
		$infinite_pricing = $this->get_infinite_pricing();

		if (
			is_null( $infinite_pricing )
			||
			! isset( $infinite_pricing->websites )
		) {
			return 0;
		}

		return (int) $infinite_pricing->websites;
	}
}
