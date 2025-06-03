<?php
namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Admin;

use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Table\PreconnectExternalDomains as PreconnectExternalDomainsTable;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options as Options_API;

class Settings {
	/**
	 * PreconnectExternalDomainsTable Instance.
	 *
	 * @var PreconnectExternalDomainsTable
	 */
	private $table;

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;


	/**
	 * WP Rocket Options API Instance.
	 *
	 * @var Options_API
	 */
	private $options_api;

	/**
	 * Constructor for the Settings class.
	 *
	 * Initializes the Settings instance with a PreconnectExternalDomainsTable object.
	 *
	 * @param PreconnectExternalDomainsTable $table The table instance used to manage preconnect external domains.
	 * @param Options_Data                   $options Instance of the Option_Data class.
	 * @param Options_API                    $options_api WP Rocket Options API instance.
	 */
	public function __construct( PreconnectExternalDomainsTable $table, Options_Data $options, Options_API $options_api ) {
		$this->table       = $table;
		$this->options     = $options;
		$this->options_api = $options_api;
	}

	/**
	 * Clears the preconnect external domains cache if relevant settings have changed.
	 *
	 * This method compares the old and new settings arrays, and if changes affecting
	 * preconnect external domains are detected, it triggers a cache clear or update.
	 *
	 * @param array $old_settings The previous settings values.
	 * @param array $new_settings The new settings values.
	 *
	 * @return void
	 */
	public function maybe_clear_preconnect_external_domains( array $old_settings, array $new_settings ): void {
		$keys = [
			'minify_css',
			'minify_js',
			'exclude_css',
			'exclude_js',
			'cdn',
			'cdn_cnames',
			'host_fonts_locally',
		];
		foreach ( $keys as $key ) {
			if ( $this->did_setting_change( $key, $old_settings, $new_settings ) ) {
				$this->table->truncate_table();
				break;
			}
		}
	}

	/**
	 * Checks if the given setting's value changed.
	 *
	 * @param string $setting The settings's value to check in the old and new values.
	 * @param mixed  $old_value Old option value.
	 * @param mixed  $value     New option value.
	 *
	 * @return bool
	 */
	private function did_setting_change( $setting, $old_value, $value ) {
		return (
			array_key_exists( $setting, $old_value )
			&&
			array_key_exists( $setting, $value )
			&&
			$old_value[ $setting ] !== $value[ $setting ]
		);
	}

	/**
	 * Removes old DNS prefetch values from settings.
	 *
	 * @return void
	 */
	public function maybe_clear_dns_prefetch_values(): void {
		$options = $this->options_api->get( 'settings', [] );
		if ( empty( $options['dns_prefetch'] ) ) {
			return;
		}

		$this->options->set( 'dns_prefetch', [] );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
