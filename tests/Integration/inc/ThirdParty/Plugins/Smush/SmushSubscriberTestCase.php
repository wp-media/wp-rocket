<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Smush;

use Smush\Core\Settings;
use WPMedia\PHPUnit\Integration\TestCase;

abstract class SmushSubscriberTestCase extends TestCase {
	protected $subscriber;
	protected $options_data;
	protected $options_api;
	protected $smush;
	protected $smush_settings_option_name = '';
	protected $smush_settings = [];
	protected $smush_lazy_option_name = '';
	protected $smush_lazy = [];

	public function set_up() {
		parent::set_up();

		$this->assertTrue( class_exists( '\Smush\Core\Settings' ), 'Smush plugin not loaded' );

		$container = apply_filters( 'rocket_container', null );

		$this->subscriber   = $container->get( 'smush_subscriber' );
		$this->options_data = $container->get( 'options' );
		$this->options_api  = $container->get( 'options_api' );

		$this->smush                      = Settings::get_instance();
		$this->smush_settings_option_name = 'wp-smush-settings';
		$this->smush_settings             = $this->smush->get_setting( $this->smush_settings_option_name );
		$this->smush_lazy_option_name     = 'wp-smush-lazy_load';
		$this->smush_lazy                 = $this->smush->get_setting( $this->smush_lazy_option_name );
	}

	public function tear_down() {
		parent::tear_down();

		$this->options_api->set( 'settings', $this->options_data->get_options() );
		$this->set_reflective_property( $this->options_data, 'options', $this->subscriber );

		// Added by \Smush\Core\Settings::__construct().
		remove_action( 'wp_ajax_save_settings', [ $this->subscriber, 'save' ] );
		remove_action( 'wp_ajax_reset_settings', [ $this->subscriber, 'reset' ] );

		$this->smush->set_setting( $this->smush_settings_option_name, $this->smush_settings );
		$this->smush->set_setting( $this->smush_lazy_option_name, $this->smush_lazy );

		$this->subscriber     = null;
		$this->options_data   = null;
		$this->options_api    = null;
		$this->smush_settings = [];
		$this->smush_lazy     = [];
	}

	protected function setSmushSettings( $lazyload_enabled, array $lazyload_formats ) {
		$settings              = $this->smush_settings;
		$settings['lazy_load'] = (bool) $lazyload_enabled;

		$this->smush->set_setting( $this->smush_settings_option_name, $settings );

		$settings           = (array) $this->smush_lazy;
		$settings['format'] = $lazyload_formats;

		$this->smush->set_setting( $this->smush_lazy_option_name, $settings );

		$this->smush->init();
	}
}
