<?php
namespace WP_Rocket\Subscriber\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manage WP Rocket custom capabilities
 *
 * @since 3.4
 * @author Remy Perona
 */
class Capabilities_Subscriber implements Subscriber_Interface {
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
		'rocket_purge_opcache',
		'rocket_purge_cloudflare_cache',
		'rocket_purge_sucuri_cache',
		'rocket_preload_cache',
		'rocket_regenerate_critical_css',
	];

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'option_page_capability_' . WP_ROCKET_PLUGIN_SLUG => 'required_capability',
			'ure_built_in_wp_caps'         => 'add_caps_to_ure',
			'ure_capabilities_groups_tree' => 'add_group_to_ure',
		];
	}

	/**
	 * Add WP Rocket capabilities to the administrator role
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_rocket_capabilities() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
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
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function remove_rocket_capabilities() {
		$role = get_role( 'administrator' );

		if ( ! $role ) {
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
	 * @author Remy Perona
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
	 * @author Remy Perona
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
	 * @author Remy Perona
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
	 * Gets the WP Rocket capabilities
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function get_capabilities() {
		return $this->capabilities;
	}
}
