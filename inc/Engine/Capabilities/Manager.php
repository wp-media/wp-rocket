<?php

namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;

class Manager implements ActivationInterface, DeactivationInterface {
	/**
	 * List of WP Rocket capabilities
	 *
	 * @var array
	 */
	private $capabilities = [
		'rocket_manage_options',
		'rocket_purge_cache',
		'rocket_purge_posts',
		'rocket_purge_terms',
		'rocket_purge_users',
		'rocket_purge_cloudflare_cache',
		'rocket_purge_sucuri_cache',
		'rocket_preload_cache',
		'rocket_regenerate_critical_css',
		'rocket_remove_unused_css',
	];

	/**
	 * Gets the WP Rocket capabilities
	 *
	 * @since 3.4
	 *
	 * @return array
	 */
	private function get_capabilities() {
		return $this->capabilities;
	}

	/**
	 * Performs these actions during the plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'add_rocket_capabilities' ] );
	}

	/**
	 * Performs these actions during the plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'rocket_deactivation', [ $this, 'remove_rocket_capabilities' ] );
	}

	/**
	 * Add WP Rocket capabilities to the administrator role
	 *
	 * @since 3.4
	 *
	 * @return void
	 */
	public function add_rocket_capabilities() {
		$role = $this->get_administrator_role_object();

		if ( is_null( $role ) ) {
			return;
		}

		foreach ( $this->get_capabilities() as $cap ) {
			$role->add_cap( $cap );
		}
	}

	/**
	 * Remove WP Rocket capabilities from the administrator role
	 *
	 * @since 3.4
	 *
	 * @return void
	 */
	public function remove_rocket_capabilities() {
		$role = $this->get_administrator_role_object();

		if ( is_null( $role ) ) {
			return;
		}

		foreach ( $this->get_capabilities() as $cap ) {
			$role->remove_cap( $cap );
		}
	}

	/**
	 * Sets the capability for the options page.
	 *
	 * @since 3.4
	 *
	 * @param string $capability The capability used for the page, which is manage_options by default.
	 * @return string
	 */
	public function required_capability( $capability ) {
		return 'rocket_manage_options';
	}

	/**
	 * Add WP Rocket capabilities to User Role Editor
	 *
	 * @since 3.4
	 *
	 * @param array $caps Array of existing capabilities.
	 * @return array
	 */
	public function add_caps_to_ure( $caps ) {
		foreach ( $this->get_capabilities() as $cap ) {
			$caps[ $cap ] = [
				'custom',
				'wp_rocket',
			];
		}

		return $caps;
	}

	/**
	 * Add WP Rocket as a group in User Role Editor
	 *
	 * @since 3.4
	 *
	 * @param array $groups Array of existing groups.
	 * @return array
	 */
	public function add_group_to_ure( $groups ) {
		$groups['wp_rocket'] = [
			'caption' => esc_html( 'WP Rocket' ),
			'parent'  => 'custom',
			'level'   => 2,
		];

		return $groups;
	}

	/**
	 * Adds WP Rocket capabilities on plugin upgrade
	 *
	 * @since 3.6.3
	 *
	 * @param string $wp_rocket_version Latest WP Rocket version.
	 * @param string $actual_version Installed WP Rocket version.
	 * @return void
	 */
	public function add_capabilities_on_upgrade( $wp_rocket_version, $actual_version ) {
		if ( version_compare( $actual_version, '3.9', '<' ) ) {
			$this->add_rocket_capabilities();
		}
	}

	/**
	 * Returns the object for the administrator roll
	 *
	 * @since 3.6.3
	 *
	 * @return WP_Role|null
	 */
	private function get_administrator_role_object() {
		return get_role( 'administrator' );
	}
}
