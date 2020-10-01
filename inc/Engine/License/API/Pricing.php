<?php

namespace WP_Rocket\Engine\License\API;

class Pricing {
	private $pricing;

	public function __construct( $pricing ) {
		$this->pricing = $pricing;
	}

	public function get_single_pricing() {
		return $this->pricing->licenses->single;
	}

	public function get_plus_pricing() {
		return $this->pricing->licenses->plus;
	}

	public function get_infinite_pricing() {
		return $this->pricing->licenses->infinite;
	}

	public function get_renewals_data() {
		return $this->pricing->renewals;
	}

	public function get_promo_data() {
		return $this->pricing->promo;
	}

	public function is_promo_active() {
		$promo_data   = $this->get_promo_data();
		$current_time = time();

		return (
			$promo_data->start_date < $current_time
			&&
			$promo_data->end_date > $current_time
		);
	}

	public function get_promo_end() {
		return $this->get_promo_data()->end_date;
	}

	public function get_regular_single_to_plus_price() {
		return $this->get_plus_pricing()->prices->from_single->regular;
	}

	public function get_single_to_plus_price() {
		return $this->is_promo_active() ? $this->get_plus_pricing()->prices->from_single->sale : $this->get_plus_pricing()->prices->from_single->regular;
	}

	public function get_regular_single_to_infinite_price() {
		return $this->get_infinite_pricing()->prices->from_single->regular;
	}

	public function get_single_to_infinite_price() {
		return $this->is_promo_active() ? $this->get_infinite_pricing()->prices->from_single->sale : $this->get_infinite_pricing()->prices->from_single->regular;
	}

	public function get_regular_plus_to_infinite_price() {
		return $this->get_infinite_pricing()->prices->from_plus->regular;
	}

	public function get_plus_to_infinite_price() {
		return $this->is_promo_active() ? $this->get_infinite_pricing()->prices->from_plus->sale : $this->get_infinite_pricing()->prices->from_plus->regular;
	}
}
