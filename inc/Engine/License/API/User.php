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
		$this->user = $user;
	}

	/**
	 * Gets the user license type
	 *
	 * @return int
	 */
	public function get_license_type() {
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->licence_account )
		) {
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
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->licence_expiration )
		) {
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
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->date_created )
		) {
			return 0;
		}

		return (int) $this->user->date_created;
	}

	/**
	 * Checks if user has auto-renew enabled
	 *
	 * @return boolean
	 */
	public function is_auto_renew() {
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->has_auto_renew )
		) {
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
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->upgrade_plus_url )
		) {
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
		if (
			! is_object( $this->user )
			||
			! isset( $this->user->upgrade_infinite_url )
		) {
			return '';
		}

		return $this->user->upgrade_infinite_url;
	}
}
