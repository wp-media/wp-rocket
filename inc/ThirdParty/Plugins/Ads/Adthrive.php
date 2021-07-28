<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Ads;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Adthrive implements Subscriber_Interface {
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
	 * @param Options      $options_api Options API instance.
	 * @param Options_Data $options Options_Data instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		$events = [];

		if ( is_plugin_active( 'adthrive-ads/adthrive-ads.php' ) ) {
			$events['admin_init'] = 'add_delay_js_exclusion';
		}

		$events['activate_adthrive-ads/adthrive-ads.php'] = 'add_delay_js_exclusion';

		return $events;
	}

	/**
	 * Adds adthrive to delay JS exclusion field
	 *
	 * @since 3.9.2
	 *
	 * @return void
	 */
	public function add_delay_js_exclusion() {
		if ( ! $this->options->get( 'delay_js', 0 ) ) {
			return;
		}

		$exclusions = $this->options->get( 'delay_js_exclusions', [] );

		if ( in_array( 'adthrive', $exclusions, true ) ) {
			return;
		}

		$exclusions[] = 'adthrive';

		$this->options->set( 'delay_js_exclusions', $exclusions );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}
}
