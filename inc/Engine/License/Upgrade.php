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
			'upgrades' => $this->get_upgrade_choices(),
		];

		echo $this->generate( 'upgrade-popin', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		return [
			'name'        => 'Plus',
			'price'       => $this->pricing->get_single_to_plus_price(),
			'websites'    => $this->pricing->get_plus_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_plus_url(),
		];
	}

	/**
	 * Gets the data to upgrade from single to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_single_to_infinite_data() {
		return [
			'name'        => 'Infinite',
			'price'       => $this->pricing->get_single_to_infinite_price(),
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];
	}

	/**
	 * Gets the data to upgrade from plus to infinite
	 *
	 * @return array
	 */
	private function get_upgrade_from_plus_to_infinite_data() {
		return [
			'name'        => 'Infinite',
			'price'       => $this->pricing->get_plus_to_infinite_price(),
			'websites'    => $this->pricing->get_infinite_websites_count(),
			'upgrade_url' => $this->user->get_upgrade_infinite_url(),
		];
	}
}
