<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Abstract_Render;
use DateTimeZone;
use DateTime;
use WP_Rocket\Engine\License\API\User;

class Shutdown extends Abstract_Render {

	/**
	 * GMT Shutdown date.
	 *
	 * @var string
	 */
	private $shutdown_date = '2022-05-10';

	private $discount_percentage = 20;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Instantiate the class
	 *
	 * @param User    $user          User instance.
	 * @param string  $template_path Path to the views.
	 */
	public function __construct( User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->user    = $user;
	}

	public function display_shutdown_banner() {
		// Will compare the current GMT timestamp with the shutdown date timestamp.
		$timezone = new DateTimeZone( 'UTC' );
		$now = new DateTime( 'now', $timezone );
		$shutdown_date = new DateTime( $this->shutdown_date, $timezone );

		if ( $shutdown_date <= $now ) {
			$this->display_after_shutdown_rucss_banner();
			return;
		}

		$this->display_before_shutdown_rucss_banner( $shutdown_date );
	}

	private function display_before_shutdown_rucss_banner( $shutdown_date ) {
		$data = [
			'formatted_date'      => $shutdown_date->format( 'M j, o' ),
			'discount_percentage' => $this->discount_percentage,
			'renewal_url'         => $this->user->get_renewal_url(),
		];
		echo $this->generate( 'before', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function display_after_shutdown_rucss_banner() {
		$data = [];
		echo $this->generate( 'after', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}
