<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Database instance
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Settings instance.
	 * @param Database $database Database instance.
	 */
	public function __construct( Settings $settings, Database $database ) {
		$this->settings = $settings;
		$this->database = $database;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		return [
			'rocket_first_install_options' => [
				[ 'add_options_first_time' ],
				[ 'instantiate_rucss_database_tables' ],
			],
			'wp_rocket_upgrade'            => [
				[ 'set_option_on_update', 13, 2 ],
				[ 'instantiate_rucss_database_tables_on_update', 13, 2 ],
			],
		];
	}

	/**
	 * Add the RUCSS options to the WP Rocket options array.
	 *
	 * @since 3.9
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options_first_time( $options ) : array {
		return $this->settings->add_options( $options );
	}

	/**
	 * Sets the RUCSS options to defaults when updating to 3.9
	 *
	 * @since 3.9
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );
	}

	/**
	 * Instantiate DB tables required by RUCSS.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function instantiate_rucss_database_tables() {
		$this->database->instantiate_rucss_database_tables();
	}

	/**
	 * Instantiates RUCSS DB tables when updating to 3.9
	 *
	 * @since 3.9
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function instantiate_rucss_database_tables_on_update( $new_version, $old_version ) {
		$this->database->instantiate_rucss_database_tables_on_update( $old_version );
	}
}
