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

		if ( ! $this->pricing->is_promo_active() ) {
			return $menu_title;
		}

		if ( get_user_meta( get_current_user_id(), 'rocket_promo_seen', true ) ) {
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

		if ( get_user_meta( $user_id, 'rocket_promo_seen', true ) ) {
			return;
		}

		add_user_meta( $user_id, 'rocket_promo_seen', 1, true );
	}

	/**
	 * Schedules a reset of the user meta when a promo ends to be ready for the next one
	 *
	 * @return void
	 */
	public function schedule_promo_reset() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		if ( ! $this->pricing->is_promo_active() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_schedule_promo_reset' ) ) {
			return;
		}

		wp_schedule_single_event( $this->pricing->get_promo_end(), 'rocket_schedule_promo_reset' );
	}

	/**
	 * Deletes the user meta preventing the notification bubble from showing
	 *
	 * @return void
	 */
	public function reset_promo_user_meta() {
		delete_metadata( 'user', 0, 'rocket_promo_seen', null, true );
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
		$choices = [];
		$license = $this->user->get_license_type();

		if ( $license === $this->pricing->get_single_websites_count() ) {
			$choices['plus']     = $this->get_upgrade_from_single_to_plus_data();
			$choices['infinite'] = $this->get_upgrade_from_single_to_infinite_data();
		} elseif ( $license === $this->pricing->get_plus_websites_count() ) {
			$choices['infinite'] = $this->get_upgrade_from_plus_to_infinite_data();
		}

		return $choices;
	}

	/**
	 * Gets the data to upgrade from single to plus
	 *
	 * @return array
	 */
	private function get_upgrade_from_single_to_plus_data() {
		$data = [
			'name'        => 'Plus',
			'price'       => $this->pricing->get_single_to_plus_price(),
			'websites'    => $this->pricing->get_plus_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_plus_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$data['regular_price'] = $this->pricing->get_regular_single_to_plus_price();
		}

		return $data;
	}

	/**
	 * Gets the data to upgrade from single to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_single_to_infinite_data() {
		$data = [
			'name'        => 'Infinite',
			'price'       => $this->pricing->get_single_to_infinite_price(),
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$data['regular_price'] = $this->pricing->get_regular_single_to_infinite_price();
		}

		return $data;
	}

	/**
	 * Gets the data to upgrade from plus to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_plus_to_infinite_data() {
		$data = [
			'name'        => 'Infinite',
			'price'       => $this->pricing->get_plus_to_infinite_price(),
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];

		if ( $this->pricing->is_promo_active() ) {
			$data['regular_price'] = $this->pricing->get_regular_plus_to_infinite_price();
		}

		return $data;
	}
}
