<?php

namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;
use WP_Rocket\Tests\TestCredentials;

abstract class ApiTestCase extends WPMediaRESTfulTestCase {
	use HttpRequestTrait;
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::uninstallAll();
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Any HTTP request a test doesn't mock fails it.
		$this->setup_http();

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_key' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_key' ] );

		parent::tear_down();

		// Last, so an unmocked-request failure can't skip the cleanup above.
		$this->tear_down_http();
	}

	public function set_email() {
		return TestCredentials::EMAIL;
	}

	public function set_key() {
		return TestCredentials::KEY;
	}
}
