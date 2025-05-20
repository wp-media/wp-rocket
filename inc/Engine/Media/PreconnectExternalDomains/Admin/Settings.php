<?php
namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Admin;

use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Table\PreconnectExternalDomains as PreconnectExternalDomainsTable;

class Settings {
	/**
	 * PreconnectExternalDomainsTable Instance.
	 *
	 * @var PreconnectExternalDomainsTable
	 */
	private $table;

	/**
	 * Constructor for the Settings class.
	 *
	 * Initializes the Settings instance with a PreconnectExternalDomainsTable object.
	 *
	 * @param PreconnectExternalDomainsTable $table The table instance used to manage preconnect external domains.
	 */
	public function __construct( PreconnectExternalDomainsTable $table ) {
		$this->table = $table;
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
			if ( $this->did_setting_change( $key, $old, $new ) ) {
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
}
