<?php

namespace WP_Rocket\Engine\License\API;

class User {
	private $user;

	public function __construct( $user ) {
		$this->user = $user;
	}

	public function get_license_type() {
		return (int) $this->user->licence_account;
	}

	public function get_license_expiration() {
		return $this->user->licence_expiration;
	}

	public function is_license_expired() {
		return time() > $this->get_license_expiration();
	}

	public function get_creation_date() {
		return $this->user->date_created;
	}

	public function is_auto_renew() {
		return (bool) $this->user->has_auto_renew;
	}

	public function get_upgrade_plus_url() {
		return $this->user->upgrade_plus_url;
	}

	public function get_upgrade_infinite_url() {
		return $this->user->upgrade_infinite_url;
	}
}
