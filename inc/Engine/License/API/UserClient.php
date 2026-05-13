<?php

namespace WP_Rocket\Engine\License\API;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;

class UserClient extends AbstractSafeAPIClient {
	const USER_ENDPOINT = 'https://api.wp-rocket.me/stat/1.0/wp-rocket/user.php';

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Get the transient key for plugin updates.
	 *
	 * This method returns the transient key used for caching plugin updates.
	 *
	 * @return string The transient key for plugin updates.
	 */
	protected function get_transient_key() {
		return 'wpr_user_information';
	}

	/**
	 * Get the API URL for plugin updates.
	 *
	 * This method returns the API URL used for fetching plugin updates.
	 *
	 * @return string The API URL for plugin updates.
	 */
	protected function get_api_url() {
		return self::USER_ENDPOINT;
	}

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Gets user data from cache if it exists, else gets it from the user endpoint
	 *
	 * Cache the user data for 24 hours in a transient
	 *
	 * @since 3.7.3
	 *
	 * @return bool|object
	 */
	public function get_user_data() {
		$cached_data = get_transient( 'wp_rocket_customer_data' );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		$data = $this->get_local_user_data();

		set_transient( 'wp_rocket_customer_data', $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Gets local account data for builds that do not use remote license validation.
	 *
	 * @return object
	 */
	private function get_local_user_data() {
		return (object) [
			'ID'                     => 'local-build',
			'first_name'             => 'Local',
			'email'                  => '',
			'date_created'           => time(),
			'licence_account'        => 999,
			'licence_expiration'     => time() + ( 10 * 365 * DAY_IN_SECONDS ),
			'status'                 => 'active',
			'is_blacklisted'         => '',
			'is_blocked'             => '',
			'has_auto_renew'         => true,
			'renewal_url'            => '',
			'upgrade_plus_url'       => '',
			'upgrade_infinite_url'   => '',
			'licence'                => (object) [
				'is_revoked' => false,
			],
			'performance_monitoring' => (object) [
				'expiration' => time() + ( 10 * 365 * DAY_IN_SECONDS ),
			],
		];
	}

	/**
	 * Flushes the user data cache.
	 *
	 * @return void
	 */
	public function flush_cache() {
		delete_transient( 'wp_rocket_customer_data' );
	}
}
