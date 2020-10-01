<?php

namespace WP_Rocket\Engine\License;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;

class Upgrade extends Abstract_Render {
	private $pricing;
	private $user;

	public function __construct( Pricing $pricing, User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->pricing = $pricing;
		$this->user    = $user;
	}

	public function display_upgrade_section() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		echo $this->generate( 'upgrade-section' );
	}

	public function display_upgrade_popin() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		$data = [
			'is_promo_active' => $this->pricing->is_promo_active(),
			'upgrades'        => $this->get_upgrade_choices(),
		];

		echo $this->generate( 'upgrade-popin', $data );
	}

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

	public function dismiss_notification_bubble() {
		if ( ! $this->can_upgrade() ) {
			return;
		}

		if ( ! $this->pricing->is_promo_active() ) {
			return;
		}

		if ( get_user_meta( get_current_user_id(), 'rocket_promo_seen', true ) ) {
			return;
		}

		add_user_meta( get_current_user_id(), 'rocket_promo_seen', 1, true );
	}

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

	public function reset_promo_user_meta() {
		delete_metadata( 'user', 0, 'rocket_promo_seen', null, true );
	}

	private function can_upgrade() {
		return (
			-1 !== $this->user->get_license_type()
			&&
			! $this->user->is_license_expired()
		);
	}

	private function get_upgrade_choices() {
		$choices = [];
		$license = $this->user->get_license_type();

		if ( $license === (int) $this->pricing->get_single_pricing()->websites ) {
			$choices['plus'] = $this->get_upgrade_from_single_to_plus_data();
			$choices['infinite'] = $this->get_upgrade_from_single_to_infinite_data();
		} elseif ( $license === (int) $this->pricing->get_plus_pricing()->websites ) {
			$choices['infinite'] = $this->get_upgrade_from_plus_to_infinite_data();
		}

		return $choices;
	}

	private function get_upgrade_from_single_to_plus_data() {
		return [
			'name'          => 'Plus',
			'price'         => $this->pricing->get_single_to_plus_price(),
			'regular_price' => $this->pricing->get_regular_single_to_plus_price(),
			'websites'      => $this->pricing->get_plus_pricing()->websites,
			'upgrade_url'   => $this->user->get_upgrade_plus_url(),
		];
	}

	private function get_upgrade_from_single_to_infinite_data() {
		return [
			'name'          => 'Infinite',
			'price'         => $this->pricing->get_single_to_infinite_price(),
			'regular_price' => $this->pricing->get_regular_single_to_infinite_price(),
			'websites'      => $this->pricing->get_infinite_pricing()->websites,
			'upgrade_url'   => $this->user->get_upgrade_infinite_url(),
		];
	}

	private function get_upgrade_from_plus_to_infinite_data() {
		return [
			'name'          => 'Infinite',
			'price'         => $this->pricing->get_plus_to_infinite_price(),
			'regular_price' => $this->pricing->get_regular_plus_to_infinite_price(),
			'websites'      => $this->pricing->get_infinite_pricing()->websites,
			'upgrade_url'   => $this->user->get_upgrade_infinite_url(),
		];
	}
}
