<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\License\API\User;

class RenewalNotice extends Abstract_Render {
	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Constructor
	 *
	 * @param User   $user User instance.
	 * @param string $template_path Template path.
	 */
	public function __construct( User $user, string $template_path ) {
		parent::__construct( $template_path );

		$this->user = $user;
	}

	/**
	 * Display the renewal notice on plugins page
	 *
	 * @param string $version Latest version number.
	 *
	 * @return void
	 */
	public function renewal_notice( $version ) {
		if ( ! $this->user->is_license_expired() ) {
			return;
		}

		if ( ! $this->is_major_version_available( $version ) ) {
			return;
		}

		$major = $this->extract_major( $version );

		$data = [
			'version'     => $major,
			'release_url' => 'https://wp-rocket.me/blog/wp-rocket-' . str_replace( '.', '-', $major ) . '/',
			'renew_url'   => $this->user->get_renewal_url(),
		];

		echo $this->generate( 'update-renewal-expired-notice', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Adds styles for expired banner
	 *
	 * @param string $version Latest version number.
	 *
	 * @return void
	 */
	public function add_expired_styles( $version ) {
		if ( ! $this->user->is_license_expired() ) {
			return;
		}

		if ( ! $this->is_major_version_available( $version ) ) {
			return;
		}

		echo '<style>.plugins tr[data-slug=wp-rocket] th, .plugins tr[data-slug=wp-rocket] td {box-shadow: none !important;}.notice.wp-rocket-update{border-color:#d63638;background-color:#fbf9e8;}.plugin-update .notice.wp-rocket-update a{color:#2782ad;}.wp-rocket-update p::before{display: inline-block;font: normal 20px/1 dashicons;-webkit-font-smoothing: antialiased;-moz-osx-font-smoothing: grayscale;vertical-align: top;color: #d63638;content: "\f534";}@media screen and (max-width: 782px){.plugins tr[data-slug=wp-rocket].active + .plugin-update-tr::before {background-color: #f0f6fc;border-left: 4px solid #72aee6;}}</style>';
	}

	/**
	 * Checks if a new major version is available
	 *
	 * @param string $version Version available from the API.
	 *
	 * @return bool
	 */
	private function is_major_version_available( $version ): bool {
		$current_version = rocket_get_constant( 'WP_ROCKET_VERSION', '' );

		$current_major = $this->extract_major( $current_version );
		$version_major = $this->extract_major( $version );

		return version_compare( $current_major, $version_major, '<' );
	}

	/**
	 * Extracts the major version number from the provided version
	 *
	 * @param string $version Version number.
	 *
	 * @return string
	 */
	private function extract_major( $version ): string {
		$parts = explode( '.', $version );

		return $parts[0] . '.' . $parts[1];
	}
}
