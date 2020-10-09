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
		if ( ! $this->can_upgrade() ) {
			return $menu_title;
		}

		if ( $this->is_expired_soon() ) {
			return;
		}

		if ( ! $this->pricing->is_promo_active() ) {
			return $menu_title;
		}

		if ( get_transient( 'rocket_promo_seen_' . get_current_user_id() ) ) {
			return $menu_title;
		}

		return $menu_title . '<span class="awaiting-mod">!</span>';
	}

	/**
	 * Prevents the notification bubble from showing once the user accessed the dashboard once
	 *
	 * @return void
	 */
	public function dismiss_notification_bubble() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		if ( ! $this->pricing->is_promo_active() ) {
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
		if ( ! $this->can_upgrade() ) {
			return;
		}

		if ( $this->is_expired_soon() ) {
			return;
		}

		if ( ! $this->pricing->is_promo_active() ) {
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
		];

		echo $this->generate( 'promo-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * AJAX callback to dismiss the promotion banner
	 *
	 * @return void
	 */
	public function dismiss_promo_banner() {
		check_ajax_referer( 'rocket_promo_dismiss', 'nonce', true );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$user = get_current_user_id();

		if ( false !== get_transient( "rocket_promo_banner_{$user}" ) ) {
			return;
		}

		set_transient( "rocket_promo_banner_{$user}", 1, 2 * WEEK_IN_SECONDS );
	}

	/**
	 * Checks if the license expires in less than 30 days
	 *
	 * @return boolean
	 */
	private function is_expired_soon() {
		$expiration_delay = time() - $this->user->get_license_expiration();

		return 30 * DAY_IN_SECONDS > $expiration_delay;
	}

	/**
	 * Checks if the current license can upgrade
	 *
	 * @return boolean
	 */
	private function can_upgrade() {
		return (
			-1 !== $this->user->get_license_type()
			&&
			! $this->user->is_license_expired()
		);
	}

	/**
	 * Gets the upgrade choices depending on the current license level
	 *
	 * @return array
	 */
	private function get_upgrade_choices() {
		$choices       = [];
		$license       = $this->user->get_license_type();
		$plus_websites = $this->pricing->get_plus_websites_count();

		if ( $license === $plus_websites ) {
			$choices['infinite'] = $this->get_upgrade_from_plus_to_infinite_data();
		} elseif (
			$license >= $this->pricing->get_single_websites_count()
			&&
			$license < $plus_websites
			) {
			$choices['plus']     = $this->get_upgrade_from_single_to_plus_data();
			$choices['infinite'] = $this->get_upgrade_from_single_to_infinite_data();
		}

		return $choices;
	}

	/**
	 * Gets the data to upgrade from single to plus
	 *
	 * @return array
	 */
	private function get_upgrade_from_single_to_plus_data() {
		$price = $this->pricing->get_single_to_plus_price();
		$data  = [
			'name'        => 'Plus',
			'price'       => $price,
			'websites'    => $this->pricing->get_plus_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_plus_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$regular_price         = $this->pricing->get_regular_single_to_plus_price();
			$data['saving']        = $regular_price - $price;
			$data['regular_price'] = $regular_price;
		}

		return $data;
	}

	/**
	 * Gets the data to upgrade from single to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_single_to_infinite_data() {
		$price = $this->pricing->get_single_to_infinite_price();
		$data  = [
			'name'        => 'Infinite',
			'price'       => $price,
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$regular_price         = $this->pricing->get_regular_single_to_infinite_price();
			$data['saving']        = $regular_price - $price;
			$data['regular_price'] = $regular_price;
		}

		return $data;
	}

	/**
	 * Gets the data to upgrade from plus to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_plus_to_infinite_data() {
		$price = $this->pricing->get_plus_to_infinite_price();
		$data  = [
			'name'        => 'Infinite',
			'price'       => $price,
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$regular_price         = $this->pricing->get_regular_plus_to_infinite_price();
			$data['saving']        = $regular_price - $price;
			$data['regular_price'] = $regular_price;
		}

		return $data;
	}
}
