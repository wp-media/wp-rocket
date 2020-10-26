<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Renewal extends Abstract_Render {
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

	/**
	 * Displays the renewal banner for users expiring in less than 30 days
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_soon_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( $this->user->is_license_expired() ) {
			return;
		}

		if ( ! $this->is_expired_soon() ) {
			return;
		}

		$data              = $this->get_banner_data();
		$data['countdown'] = $this->get_countdown_data();

		echo $this->generate( 'renewal-soon-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays the renewal banner for expired users
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function display_renewal_expired_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( 0 === $this->user->get_license_expiration() ) {
			return;
		}

		if ( ! $this->user->is_license_expired() ) {
			return;
		}

		if ( false !== get_transient( 'rocket_renewal_banner_' . get_current_user_id() ) ) {
			return;
		}

		echo $this->generate( 'renewal-expired-banner', $this->get_banner_data() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get base data to display in the banners
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	private function get_banner_data() {
		return [
			'discount_percent' => $this->get_discount_percent(),
			'discount_price'   => number_format_i18n( $this->get_discount_price(), 2 ),
			'renewal_url'      => $this->user->get_renewal_url(),
		];
	}

	/**
	 * AJAX callback to dismiss the renewal banner for expired users
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function dismiss_renewal_expired_banner() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$transient = 'rocket_renewal_banner_' . get_current_user_id();

		if ( false !== get_transient( $transient ) ) {
			return;
		}

		set_transient( $transient, 1, MONTH_IN_SECONDS );

		wp_send_json_success();
	}

	/**
	 * Adds the license expiration time to WP Rocket localize script data
	 *
	 * @since 3.7.5
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_localize_script_data( $data ) {
		if ( ! is_array( $data ) ) {
			$data = (array) $data;
		}

		if ( $this->user->is_license_expired() ) {
			return $data;
		}

		if ( ! $this->is_expired_soon() ) {
			return $data;
		}

		$data['license_expiration'] = $this->user->get_license_expiration();

		return $data;
	}

	/**
	 * Checks if the license expires in less than 30 days
	 *
	 * @since 3.7.5
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

	/**
	 * Gets the discount percentage corresponding to the current user status
	 *
	 * @since 3.7.5
	 *
	 * @return int
	 */
	private function get_discount_percent() {
		$renewals = $this->get_user_renewal_status();

		if ( false === $renewals ) {
			return 0;
		}

		if ( $renewals['is_expired'] ) {
			return isset( $renewals['discount_percent']->is_expired ) ? $renewals['discount_percent']->is_expired : 0;
		}

		if ( $renewals['is_grandfather'] ) {
			return isset( $renewals['discount_percent']->is_grandfather ) ? $renewals['discount_percent']->is_grandfather : 0;
		}

		return isset( $renewals['discount_percent']->not_grandfather ) ? $renewals['discount_percent']->not_grandfather : 0;
	}

	/**
	 * Gets the discount price corresponding to the current user status
	 *
	 * @since 3.7.5
	 *
	 * @return int
	 */
	private function get_discount_price() {
		$renewals = $this->get_user_renewal_status();

		if ( false === $renewals ) {
			return 0;
		}

		$license = $this->get_license_pricing_data();

		if ( $renewals['is_expired'] ) {
			return isset( $license->prices->renewal->is_expired ) ? $license->prices->renewal->is_expired : 0;
		}

		if ( $renewals['is_grandfather'] ) {
			return isset( $license->prices->renewal->is_grandfather ) ? $license->prices->renewal->is_grandfather : 0;
		}

		return isset( $license->prices->renewal->not_grandfather ) ? $license->prices->renewal->not_grandfather : 0;
	}

	/**
	 * Gets the user renewal status
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
	private function get_user_renewal_status() {
		$renewals = $this->pricing->get_renewals_data();

		if ( ! isset( $renewals->extra_days, $renewals->grandfather_date, $renewals->discount_percent ) ) {
			return false;
		}

		return [
			'discount_percent' => $renewals->discount_percent,
			'is_expired'       => time() > ( $this->user->get_license_expiration() + ( $renewals->extra_days * DAY_IN_SECONDS ) ),
			'is_grandfather'   => $renewals->grandfather_date > $this->user->get_creation_date(),
		];
	}

	/**
	 * Gets the license pricing data corresponding to the user license
	 *
	 * @since 3.7.5
	 *
	 * @return object|null
	 */
	private function get_license_pricing_data() {
		$license       = $this->user->get_license_type();
		$plus_websites = $this->pricing->get_plus_websites_count();

		if ( $license === $plus_websites ) {
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

	/**
	 * Gets the countdown data to display for the renewal soon banner
	 *
	 * @since 3.7.5
	 *
	 * @return array
	 */
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
