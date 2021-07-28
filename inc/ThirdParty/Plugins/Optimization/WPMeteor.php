<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class WPMeteor implements Subscriber_Interface {
	/**
	 * Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Options_Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Options $options_api Options API instance.
	 * @param Options_Data $options Options_Data instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_delay_js_settings_field'   => 'maybe_disable_delay_js_field',
			'activate_wp-meteor/wp-meteor.php' => 'disable_delay_js',
		];
	}

	/**
	 * Disable the delay JS field when WP Meteor is active
	 *
	 * @since 3.9.2
	 *
	 * @param array $field Delay JS field data array.
	 *
	 * @return array
	 */
	public function maybe_disable_delay_js_field( $field ): array {
		if ( ! is_plugin_active( 'wp-meteor/wp-meteor.php' ) ) {
			return $field;
		}

		$field['container_class'][] = 'wpr-isDisabled';
		$field['value'] = 0;
		$field['input_attr']['disabled'] = 1;
		$field['helper'] = sprintf(
			__( 'Delay JS is currently activated in %1$s. If you want to use WP Rocket’s delay JS, disable %1$s', 'rocket' ),
			'WP Meteor'
		);

		return $field;
	}

	/**
	 * Disable delay JS option when WP Meteor is activated
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function disable_delay_js() {
		$this->options->set( 'delay_js', 0 );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
