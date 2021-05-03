<?php

namespace WP_Rocket\Engine\License\API;

class User {
	/**
	 * The user object
	 *
	 * @var object
	 */
	private $user;

	/**
	 * Instantiate the class
	 *
	 * @param object $user The user object.
	 */
	public function __construct( $user ) {
		$this->user = is_object( $user ) ? $user : new \stdClass();
	}

	/**
	 * Gets the user license type
	 *
	 * @return int
	 */
	public function get_license_type() {
		if ( ! isset( $this->user->licence_account ) ) {
			return 0;
		}

		return (int) $this->user->licence_account;
	}

	/**
	 * Gets the user license expiration timestamp
	 *
	 * @return int
	 */
	public function get_license_expiration() {
		if ( ! isset( $this->user->licence_expiration ) ) {
			return 0;
		}

		return (int) $this->user->licence_expiration;
	}

	/**
	 * Checks if the user license is expired
	 *
	 * @return boolean
	 */
	public function is_license_expired() {
		return time() > $this->get_license_expiration();
	}

	/**
	 * Gets the user license creation date
	 *
	 * @return int
	 */
	public function get_creation_date() {
		if ( ! isset( $this->user->date_created ) ) {
			return time();
		}

		return (int) $this->user->date_created > 0
			? (int) $this->user->date_created
			: time();
	}

	/**
	 * Checks if user has auto-renew enabled
	 *
	 * @return boolean
	 */
	public function is_auto_renew() {
		if ( ! isset( $this->user->has_auto_renew ) ) {
			return false;
		}

		return (bool) $this->user->has_auto_renew;
	}

	/**
	 * Gets the upgrade to plus URL
	 *
	 * @return string
	 */
	public function get_upgrade_plus_url() {
		if ( ! isset( $this->user->upgrade_plus_url ) ) {
			return '';
		}

		return $this->user->upgrade_plus_url;
	}

	/**
	 * Gets the upgrade to infinite url
	 *
	 * @return string
	 */
	public function get_upgrade_infinite_url() {
		if ( ! isset( $this->user->upgrade_infinite_url ) ) {
			return '';
		}

		return $this->user->upgrade_infinite_url;
	}

	/**
	 * Gets the renewal url
	 *
	 * @return string
	 */
	public function get_renewal_url() {
		if ( ! isset( $this->user->renewal_url ) ) {
			return '';
		}

		return $this->user->renewal_url;
	}
}
