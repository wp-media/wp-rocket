<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Admin\Options_Data;

class Settings {

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Add the RUCSS options to the WP Rocket options array
	 *
	 * @since 3.9
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options( $options ) : array {
		$options = (array) $options;

		$options['remove_unused_css']          = 0;
		$options['remove_unused_css_safelist'] = [];

		return $options;
	}

	/**
	 * Sets the RUCSS options to defaults when updating to 3.9 from older versions.
	 *
	 * @since 3.9
	 *
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $old_version ) {
		if ( version_compare( $old_version, '3.9', '>' ) ) {
			return;
		}

		$options = get_option( 'wp_rocket_settings', [] );

		$options['remove_unused_css']          = 0;
		$options['remove_unused_css_safelist'] = [];

		update_option( 'wp_rocket_settings', $options );
	}

	/**
	 * Determines if Remove Unused CSS option is enabled.
	 *
	 * @return boolean
	 */
	public function is_enabled() : bool {
		return (bool) $this->options->get( 'remove_unused_css', 0 );
	}
}
