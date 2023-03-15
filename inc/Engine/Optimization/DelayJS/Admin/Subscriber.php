<?php
declare( strict_types=1 );

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
	 * Site List instance.
	 *
	 * @var SiteList
	 */
	private $site_list;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Settings instance.
	 * @param SiteList $site_list DelayJS Site List instance.
	 */
	public function __construct( Settings $settings, SiteList $site_list ) {
		$this->settings  = $settings;
		$this->site_list = $site_list;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_first_install_options'         => [
				[ 'add_options' ],
				[ 'add_default_exclusions_options' ],
			],
			'wp_rocket_upgrade'                    => [ 'set_option_on_update', 13, 2 ],
			'rocket_input_sanitize'                => [
				[ 'sanitize_options', 13, 2 ],
				[ 'sanitize_selected_exclusions', 14 ],
			],
			'pre_update_option_wp_rocket_settings' => [ 'maybe_disable_combine_js', 11, 2 ],
			'rocket_hidden_settings_fields'        => 'add_exclusions_hidden_field',
			'rocket_after_save_dynamic_lists'      => 'refresh_exclusions_option',
		];
	}

	/**
	 * Add the delay JS options to the WP Rocket options array
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 * @since 3.7
	 */
	public function add_options( $options ): array {
		return $this->settings->add_options( $options );
	}

	/**
	 * Add the delay JS  exclusions options to the WP Rocket options array
	 * based on the default items in the list.
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 * @since 3.13
	 */
	public function add_default_exclusions_options( $options ): array {
		$default_exclusions = $this->site_list->get_default_exclusions();

		if ( empty( $default_exclusions ) ) {
			$options['delay_js_exclusions_selected']            = [];
			$options['delay_js_exclusions_selected_exclusions'] = [];

			return $options;
		}

		$options['delay_js_exclusions_selected']            = array_keys( $default_exclusions );
		$options['delay_js_exclusions_selected_exclusions'] = array_merge( ...array_values( $default_exclusions ) );

		return $options;
	}

	/**
	 * Sets the delay_js_exclusions default value for users with delay JS enabled on upgrade
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 * @since 3.7
	 *
	 * @since 3.9 Sets the delay_js_exclusions default value if delay_js is 1
	 */
	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );
	}

	/**
	 * Sanitizes Delay JS options values when the settings form is submitted
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 * @since 3.9
	 */
	public function sanitize_options( $input, AdminSettings $settings ): array {
		return $this->settings->sanitize_options( $input, $settings );
	}

	/**
	 * Sanitizes delay JS selected exclusions options when saving the settings.
	 *
	 * @since 3.13
	 *
	 * @param array $input Array of values submitted from the form.
	 *
	 * @return array
	 */
	public function sanitize_selected_exclusions( $input ) {
		return $this->site_list->sanitize_options( $input );
	}

	/**
	 * Disable combine JS option when delay JS is enabled
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 * @since 3.9
	 */
	public function maybe_disable_combine_js( $value, $old_value ): array {
		return $this->settings->maybe_disable_combine_js( $value, $old_value );
	}

	/**
	 * Add exclusions hidden field.
	 *
	 * @param array $fields Hidden fields.
	 *
	 * @return array
	 */
	public function add_exclusions_hidden_field( array $fields ) {
		$fields[] = 'delay_js_exclusion_selected_exclusions';
		return $fields;
	}

	/**
	 * Refresh exclusions option when the dynamic list is updated weekly or manually.
	 *
	 * @return void
	 */
	public function refresh_exclusions_option() {
		$this->site_list->refresh_exclusions_option();
	}
}
