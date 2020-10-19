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

		echo $this->generate( 'renewal-soon-banner', $data );
	}

	public function display_renewal_expired_banner() {
		if ( rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) ) {
			return;
		}

		if ( time() > $this->user->get_license_expiration() ) {
			return;
		}

		echo $this->generate( 'renewal-expired-banner', $data );
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
}
