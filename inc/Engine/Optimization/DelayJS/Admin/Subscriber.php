<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Settings instance.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options'         => 'add_options',
			'wp_rocket_upgrade'                    => [ 'set_option_on_update', 13, 2 ],
			'rocket_input_sanitize'                => [ 'sanitize_options', 13, 2 ],
			'pre_update_option_wp_rocket_settings' => [ 'maybe_disable_combine_js', 11, 2 ],
			'rocket_plugins_to_deactivate'         => 'add_plugins_incompatibility',
		];
	}

	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @since 3.7
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) : array {
		return $this->settings->add_options( $options );
	}

	/**
	 * Sets the delay_js_exclusions default value for users with delay JS enabled on upgrade
	 *
	 * @since 3.9 Sets the delay_js_exclusions default value if delay_js is 1
	 * @since 3.7
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
	 * Sanitizes Delay JS options values when the settings form is submitted
	 *
	 * @since 3.9
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( $input, AdminSettings $settings ) : array {
		return $this->settings->sanitize_options( $input, $settings );
	}

	/**
	 * Disable combine JS option when delay JS is enabled
	 *
	 * @since 3.9
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 */
	public function maybe_disable_combine_js( $value, $old_value ): array {
		return $this->settings->maybe_disable_combine_js( $value, $old_value );
	}

	/**
	 * Adds plugins incompatible with delay JS to the list
	 *
	 * @since 3.9.0.1
	 *
	 * @param string[] $plugins Array of recommended plugins to deactivate.
	 *
	 * @return array
	 */
	public function add_plugins_incompatibility( $plugins ): array {
		return $this->settings->add_plugins_incompatibility( $plugins );
	}
}
