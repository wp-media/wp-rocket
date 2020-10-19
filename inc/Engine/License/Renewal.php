<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Renewal {
	/**
	 * Pricing instance
	 *
	 * @var Pricing
	 */
	private $pricing;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Instantiate the class
	 *
	 * @param Pricing $pricing       Pricing instance.
	 * @param User    $user          User instance.
	 * @param string  $template_path Path to the views.
	 */
	public function __construct( Pricing $pricing, User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->pricing = $pricing;
		$this->user    = $user;
	}

	public function display_renewal_soon_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( ! $this->is_expired_soon() ) {
			return;
		}

		$data = [
			'discount_percent' => $this->get_discount_percent(),
			'discount_price'   => $this->get_discount_price(),
			'countdown'        => $this->get_countdown_data(),
			'renewal_url'      => $this->user->get_renewal_url(),
		];

		echo $this->generate( 'renewal-soon-banner', $data );
	}

	public function display_renewal_expired_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( time() > $this->user->get_license_expiration() ) {
			return;
		}

		$data = [
			'discount_percent' => $this->get_discount_percent(),
			'discount_price'   => $this->get_discount_price(),
			'renewal_url'      => $this->user->get_renewal_url(),
		];

		echo $this->generate( 'renewal-expired-banner', $data );
	}

	public function dismiss_renewal_expired_banner() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$user = get_current_user_id();

		if ( false !== get_transient( "rocket_renewal_banner_{$user}" ) ) {
			return;
		}

		set_transient( "rocket_renewal_banner_{$user}", 1, MONTH_IN_SECONDS );

		wp_send_json_success();
	}

	/**
	 * Checks if the license expires in less than 30 days
	 *
	 * @return boolean
	 */
	private function is_expired_soon() {
		if ( $this->user->is_auto_renew() ) {
			return false;
		}

		$expiration_delay = $this->user->get_license_expiration() - time();

		return 30 * DAY_IN_SECONDS > $expiration_delay;
	}

	private function get_discount_percent() {
		$renewals = $this->pricing->get_renewals_data();

		if ( ! isset( $renewals->extra_days, $renewals->grandfather_date, $renewals->discount_percent ) ) {
			return 0;
		}

		$extra   = $renewals->extra_days * DAY_IN_SECONDS;
		$current = time();

		if ( $current > ( $this->user->get_license_expiration() + $extra ) ) {
			return isset( $renewals->discount_percent->is_expired ) ? $renewals->discount_percent->is_expired : 0;
		}

		if ( $this->user->get_creation_date() > $renewals->grandfather_date ) {
			return isset( $renewals->discount_percent->not_grandfather ) ? $renewals->discount_percent->not_grandfather : 0;
		}

		return isset( $renewals->discount_percent->is_grandfather ) ? $renewals->discount_percent->is_grandfather : 0;
	}

	private function get_discount_price() {
		$renewals = $this->pricing->get_renewals_data();

		if ( ! isset( $renewals->extra_days, $renewals->grandfather_date, $renewals->discount_percent ) ) {
			return 0;
		}

		$extra   = $renewals->extra_days * DAY_IN_SECONDS;
		$current = time();
		$license = $this->get_license_pricing_data();

		if ( $current > ( $this->user->get_license_expiration() + $extra ) ) {
			return isset( $license->renewal->is_expired ) ? $license->renewal->is_expired : 0;
		}

		if ( $this->user->get_creation_date() > $renewals->grandfather_date ) {
			return isset( $license->renewal->not_grandfather ) ? $license->renewal->not_grandfather : 0;
		}

		return isset( $license->renewal->is_grandfather ) ? $license->renewal->is_grandfather : 0;
	}

	private function get_license_data() {
		$license       = $this->user->get_license_type();
		$plus_websites = $this->pricing->get_plus_websites_count();

		if ( $license === $this->pricing->get_plus_websites_count() ) {
			return $this->pricing->get_plus_pricing();
		} elseif (
			$license >= $this->pricing->get_single_websites_count()
			&&
			$license < $plus_websites
		) {
			return $this->pricing->get_single_pricing();
		}

		return $this->pricing->get_infinite_pricing();
	}

	private function get_countdown_data() {
		$data = [
			'days'    => 0,
			'hours'   => 0,
			'minutes' => 0,
			'seconds' => 0,
		];

		if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
			return $data;
		}

		$expiration = $this->user->get_license_expiration();

		if ( 0 === $expiration ) {
			return $data;
		}

		$now = date_create();
		$end = date_timestamp_set( date_create(), $expiration );

		if ( $now > $end ) {
			return $data;
		}

		$remaining = date_diff( $now, $end );
		$format    = explode( ' ', $remaining->format( '%d %H %i %s' ) );

		$data['days']    = $format[0];
		$data['hours']   = $format[1];
		$data['minutes'] = $format[2];
		$data['seconds'] = $format[3];

		return $data;
	}
}
