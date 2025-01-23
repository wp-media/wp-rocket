<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Upgrade extends Abstract_Render {
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
	 * Displays the upgrade section in the license block on the dashboard
	 *
	 * @return void
	 */
	public function display_upgrade_section() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		echo $this->generate( 'upgrade-section' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays the upgrade pop on the dashboard
	 *
	 * @return void
	 */
	public function display_upgrade_popin() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( ! $this->can_upgrade() ) {
			return;
		}

		$data = [
			'is_promo_active' => $this->pricing->is_promo_active(),
			'upgrades'        => $this->get_upgrade_choices(),
		];

		echo $this->generate( 'upgrade-popin', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Adds the notification bubble to WP Rocket menu item when a promo is active
	 *
	 * @param string $menu_title Menu title.
	 * @return string
	 */
	public function add_notification_bubble( $menu_title ) {
		if ( ! $this->can_use_promo() ) {
			return $menu_title;
		}

		if ( false !== get_transient( 'rocket_promo_seen_' . get_current_user_id() ) ) {
			return $menu_title;
		}

		return $menu_title . ' <span class="rocket-promo-bubble">!</span>';
	}

	/**
	 * Prevents the notification bubble from showing once the user accessed the dashboard once
	 *
	 * @return void
	 */
	public function dismiss_notification_bubble() {
		if ( ! $this->can_use_promo() ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( false !== get_transient( "rocket_promo_seen_{$user_id}" ) ) {
			return;
		}

		set_transient( "rocket_promo_seen_{$user_id}", 1, 2 * WEEK_IN_SECONDS );
	}

	/**
	 * Displays the promotion banner
	 *
	 * @return void
	 */
	public function display_promo_banner() {
		if ( ! $this->can_use_promo() ) {
			return;
		}

		if ( false !== get_transient( 'rocket_promo_banner_' . get_current_user_id() ) ) {
			return;
		}

		$promo          = $this->pricing->get_promo_data();
		$promo_name     = isset( $promo->name ) ? $promo->name : '';
		$promo_discount = isset( $promo->discount_percent ) ? $promo->discount_percent : 0;

		$data = [
			'name'             => $promo_name,
			'discount_percent' => $promo_discount,
			'countdown'        => $this->get_countdown_data(),
			'message'          => $this->get_promo_message( $promo_name, $promo_discount ),
		];

		echo $this->generate( 'promo-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * AJAX callback to dismiss the promotion banner
	 *
	 * @return void
	 */
	public function dismiss_promo_banner() {
		check_ajax_referer( 'rocket-ajax', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$user = get_current_user_id();

		if ( false !== get_transient( "rocket_promo_banner_{$user}" ) ) {
			return;
		}

		set_transient( "rocket_promo_banner_{$user}", 1, 2 * WEEK_IN_SECONDS );

		wp_send_json_success();
	}

	/**
	 * Adds the promotion end time to WP Rocket localize script data
	 *
	 * @since 3.7.4
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_localize_script_data( array $data ) {
		if ( ! $this->can_use_promo() ) {
			return $data;
		}

		$data['promo_end'] = $this->pricing->get_promo_end();

		return $data;
	}

	/**
	 * Returns an array containing the remaining days, hours, minutes & seconds for the promotion
	 *
	 * @since 3.7.4
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

		$promo_end = $this->pricing->get_promo_end();

		if ( 0 === $promo_end ) {
			return $data;
		}

		$now = date_create();
		$end = date_timestamp_set( date_create(), $promo_end );

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

	/**
	 * Get upgrade types
	 *
	 * @return array
	 */
	private function get_upgrade_types(): array {
		$types = [];
		foreach ( $this->get_upgrade_choices() as $choice_key => $choice ) {
			$types[] = 'stacked' === $choice_key ? end( $choice )['name'] : $choice['name'];
		}

		return $types;
	}

	/**
	 * Returns the promotion message to display in the banner
	 *
	 * @param string $promo_name     Name of the promotion.
	 * @param int    $promo_discount Discount percentage.
	 *
	 * @return string
	 */
	private function get_promo_message( $promo_name = '', $promo_discount = 0 ) {
		$license_types = $this->get_upgrade_types();

		return sprintf(
		// translators: %1$s = promotion name, %2$s = <br>, %3$s = <strong>, %4$s = promotion discount percentage, %5$s = </strong>, %6$s = Growth or Multi.
			esc_html__(
				'Take advantage of %1$s to speed up more websites:%2$s get a %3$s%4$s off%5$s for %3$supgrading your license to %6$s!%5$s',
				'rocket'
			),
			$promo_name,
			'<br>',
			'<strong>',
			$promo_discount . '%',
			'</strong>',
			implode( ' ' . esc_html__( 'or', 'rocket' ) . ' ', $license_types ),
		);
	}

	/**
	 * Checks if current user can use the promotion
	 *
	 * @since 3.7.4
	 *
	 * @return boolean
	 */
	private function can_use_promo() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return false;
		}

		if ( ! $this->can_upgrade() ) {
			return false;
		}

		if ( $this->is_expired_soon() ) {
			return false;
		}

		if ( $this->is_new_user() ) {
			return false;
		}

		return $this->pricing->is_promo_active();
	}

	/**
	 * Checks if the license expires in less than 30 days
	 *
	 * @return boolean
	 */
	private function is_expired_soon() {
		$expiration_delay = $this->user->get_license_expiration() - time();

		return 30 * DAY_IN_SECONDS > $expiration_delay;
	}

	/**
	 * Checks if the User license bought less than 14 days
	 *
	 * @return boolean
	 */
	private function is_new_user() {
		return ( 14 * DAY_IN_SECONDS ) > time() - $this->user->get_creation_date();
	}

	/**
	 * Checks if the current license can upgrade
	 *
	 * @return boolean
	 */
	private function can_upgrade() {
		return (
			! $this->user->is_license_expired()
			&&
			! empty( $this->user->get_available_upgrades() )
		);
	}

	/**
	 * Gets the upgrade choices depending on the current license level
	 *
	 * @return array
	 */
	private function get_upgrade_choices() {
		$choices = [];

		foreach ( $this->user->get_available_upgrades() as $available_upgrade ) {
			$upgrade_data = $this->get_generic_upgrade_data( $available_upgrade );

			if ( ! empty( $available_upgrade->stack ) && ! empty( $available_upgrade->slug ) ) {
				if ( ! isset( $choices['stacked'] ) ) {
					$choices['stacked'] = [];
				}
				$choices['stacked'][ $available_upgrade->slug ] = $upgrade_data;
				continue;
			}

			$choices[ $available_upgrade->slug ] = $upgrade_data;
		}

		return $choices;
	}

	/**
	 * Prepare the upgrade array based on the upgrade object from the API.
	 *
	 * @param object $upgrade_item Upgrade item object from the API.
	 * @return array
	 */
	private function get_generic_upgrade_data( $upgrade_item ) {
		$data = [
			'name'        => $upgrade_item->name,
			'price'       => $this->pricing->is_promo_active() ? $upgrade_item->saving : $upgrade_item->regular_price,
			'websites'    => $upgrade_item->websites,
			'upgrade_url' => $upgrade_item->upgrade_url,
		];

		if ( $this->pricing->is_promo_active() ) {
			$data['saving']        = $upgrade_item->regular_price - $upgrade_item->saving;
			$data['regular_price'] = $upgrade_item->regular_price;
		}

		return $data;
	}
}
