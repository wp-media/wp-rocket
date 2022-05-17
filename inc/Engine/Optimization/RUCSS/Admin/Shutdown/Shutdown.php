<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin\Shutdown;

use WP_Rocket\Abstract_Render;
use DateTimeZone;
use DateTime;
use Exception;
use WP_Rocket\Engine\License\API\User;

class Shutdown extends Abstract_Render {

	/**
	 * GMT Shutdown date.
	 *
	 * @var string
	 */
	private $shutdown_date = '2022-05-30';

	/**
	 * Renewal discount percentage.
	 *
	 * @var int
	 */
	private $discount_percentage = 20;

	/**
	 * User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Instantiate the class
	 *
	 * @param User   $user          User instance.
	 * @param string $template_path Path to the views.
	 */
	public function __construct( User $user, $template_path ) {
		parent::__construct( $template_path );

		$this->user = $user;
	}

	/**
	 * Check if shutdown notice expired or not.
	 *
	 * @param DateTime|null $shutdown_date Optional shutdown date object.
	 *
	 * @return bool
	 * @throws Exception When date is invalid.
	 */
	public function is_expired( DateTime $shutdown_date = null ) {
		$timezone = new DateTimeZone( 'UTC' );
		$now      = new DateTime( 'now', $timezone );

		if ( ! $shutdown_date ) {
			$shutdown_date = new DateTime( $this->shutdown_date, $timezone );
		}

		return $shutdown_date <= $now;
	}

	/**
	 * Display RUCSS shutdown warning banner.
	 *
	 * @return void
	 * @throws Exception When something went wrong with dates.
	 */
	public function display_shutdown_banner() {
		// Will compare the current GMT timestamp with the shutdown date timestamp.
		$timezone      = new DateTimeZone( 'UTC' );
		$shutdown_date = new DateTime( $this->shutdown_date, $timezone );

		if ( $this->is_expired( $shutdown_date ) ) {
			$this->display_after_shutdown_rucss_banner();
			return;
		}

		$this->display_before_shutdown_rucss_banner( $shutdown_date );
	}

	/**
	 * Display the banner before the shutdown date.
	 *
	 * @param DateTime $shutdown_date Shutdown date object.
	 *
	 * @return void
	 */
	private function display_before_shutdown_rucss_banner( DateTime $shutdown_date ) {
		$data = [
			'formatted_date'      => $shutdown_date->format( 'M j, o' ),
			'discount_percentage' => $this->discount_percentage,
			'renewal_url'         => $this->user->get_renewal_url(),
		];
		echo $this->generate( 'before', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Display the banner after the shutdown date.
	 *
	 * @return void
	 */
	private function display_after_shutdown_rucss_banner() {
		$data = [
			'discount_percentage' => $this->discount_percentage,
			'renewal_url'         => $this->user->get_renewal_url(),
		];
		echo $this->generate( 'after', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get shutdown event details.
	 *
	 * @param array $details Shutdown details.
	 *
	 * @return array
	 * @throws Exception When date is invalid.
	 */
	public function get_shutdown_details( array $details ) {
		if ( ! $this->is_expired() ) {
			return $details;
		}

		return [
			'status'      => 1,
			'renewal_url' => $this->user->get_renewal_url(),
		];
	}

}
