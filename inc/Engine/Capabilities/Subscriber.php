<?php
namespace WP_Rocket\Engine\Capabilities;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manage WP Rocket custom capabilities
 *
 * @since 3.4
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Capabilities manager instance
	 *
	 * @var Manager
	 */
	private $capabilities;

	/**
	 * Instantiate the subscriber
	 *
	 * @param Manager $capabilities Capabilities manager instance.
	 */
	public function __construct( Manager $capabilities ) {
		$this->capabilities = $capabilities;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$slug = rocket_get_constant( 'WP_ROCKET_PLUGIN_SLUG' );

		return [
			"option_page_capability_{$slug}" => 'required_capability',
			'ure_built_in_wp_caps'           => 'add_caps_to_ure',
			'ure_capabilities_groups_tree'   => 'add_group_to_ure',
			'members_register_cap_groups'    => 'add_cap_group_to_members',
			'members_register_caps'          => 'add_caps_to_members',
			'wp_rocket_upgrade'              => [ 'add_capabilities_on_upgrade', 12, 2 ],
		];
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
		return $this->capabilities->required_capability( $capability );
	}

	/**
	 * Adds WP Rocket capabilities to User Role Editor
	 *
	 * @since 3.4
	 *
	 * @param array $caps Array of existing capabilities.
	 * @return array
	 */
	public function add_caps_to_ure( $caps ) {
		return $this->capabilities->add_caps_to_ure( $caps );
	}

	/**
	 * Adds WP Rocket as a group in User Role Editor
	 *
	 * @since 3.4
	 *
	 * @param array $groups Array of existing groups.
	 * @return array
	 */
	public function add_group_to_ure( $groups ) {
		return $this->capabilities->add_group_to_ure( $groups );
	}

	/**
	 * Add WP Rocket as a cap group in Members
	 */
	public function add_cap_group_to_members() {
		$this->capabilities->add_cap_group_to_members();
	}

	/**
	 * Add WP Rocket capabilities to Members
	 */
	public function add_caps_to_members() {
		$this->capabilities->add_caps_to_members();
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
		$this->capabilities->add_capabilities_on_upgrade( $wp_rocket_version, $actual_version );
	}
}
