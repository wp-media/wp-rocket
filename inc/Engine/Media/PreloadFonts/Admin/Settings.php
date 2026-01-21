<?php
namespace WP_Rocket\Engine\Media\PreloadFonts\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options as Options_API;

class Settings {
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
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $option_data WP Rocket Options instance.
	 * @param Options_API  $options_api WP Rocket Options API instance.
	 */
	public function __construct( Options_Data $option_data, Options_API $options_api ) {
		$this->options     = $option_data;
		$this->options_api = $options_api;
	}

	/**
	 * Enables the auto preload fonts option if the old preload fonts option is not empty.
	 *
	 * This function checks the value of the `preload_fonts` option.
	 * If it contains a non-empty value, it updates the `auto_preload_fonts` option to `true`.
	 * This is useful for ensuring that automatic font preloading is enabled based on legacy settings.
	 *
	 * @return void
	 */
	public function maybe_enable_auto_preload_fonts(): void {
		$options = $this->options_api->get( 'settings', [] );
		if ( empty( $options['preload_fonts'] ) ) {
			return;
		}

		$this->options->set( 'auto_preload_fonts', true );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
