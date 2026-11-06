<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DelayJS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Theme;

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
			'rocket_after_save_dynamic_lists'      => 'refresh_exclusions_option',
			'activate_plugin'                      => 'add_plugin_exclusions',
			'deactivate_plugin'                    => 'remove_plugin_exclusions',
			'switch_theme'                         => [ 'handle_switch_theme_exclusions', 10, 3 ],
			'rocket_meta_boxes_fields'             => [ 'add_meta_box', 6 ],
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
	 * Refresh exclusions option when the dynamic list is updated weekly or manually.
	 *
	 * @return void
	 */
	public function refresh_exclusions_option() {
		$this->site_list->refresh_exclusions_option();
	}

	/**
	 * Remove plugin from exclusions list once deactivated.
	 *
	 * @param string $plugin Plugin basename.
	 *
	 * @return void
	 */
	public function remove_plugin_exclusions( string $plugin ) {
		if ( plugin_basename( WP_ROCKET_FILE ) === $plugin ) {
			return;
		}
		$this->site_list->remove_plugin_selection( $plugin );
	}

	/**
	 * Handle switch theme exclusions, remove the old theme exclusions and add the new one.
	 *
	 * @param string   $new_name  Name of the new theme.
	 * @param WP_Theme $new_theme WP_Theme instance of the new theme.
	 * @param WP_Theme $old_theme WP_Theme instance of the old theme.
	 *
	 * @return void
	 */
	public function handle_switch_theme_exclusions( string $new_name, WP_Theme $new_theme, WP_Theme $old_theme ) {
		$this->site_list->replace_theme_selection( $new_theme, $old_theme );
	}

	/**
	 * Add plugin exclusions with plugin activation for default checked plugins.
	 *
	 * @param string $plugin Plugin basename.
	 *
	 * @return void
	 */
	public function add_plugin_exclusions( string $plugin ) {
		if ( plugin_basename( WP_ROCKET_FILE ) === $plugin ) {
			return;
		}
		$this->site_list->add_default_plugin_exclusions( $plugin );
	}

	/**
	 * Add the field to the WP Rocket metabox on the post edit page.
	 *
	 * @param string[] $fields Metaboxes fields.
	 *
	 * @return string[]
	 */
	public function add_meta_box( array $fields ) {
		$fields['delay_js'] = __( 'Delay JavaScript execution', 'rocket' );

		return $fields;
	}
}
