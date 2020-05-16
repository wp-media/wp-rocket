<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

//use WP_Rocket\Tests\Integration\FilesystemTestCase as BaseVfsTestCase;
use WP_Rocket\Tests\Integration\TestCase as BaseVfsTestCase;

abstract class TestCase extends BaseVfsTestCase {
	protected $cdn_names;
	protected $home_url = 'http://example.org';

	protected static $transients = [
		'rocketcdn_status' => null,
	];

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tearDown() {
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		parent::tearDown();
	}

	public function home_url_cb() {
		return $this->home_url;
	}

	public function cdn_names_cb() {
		return $this->cdn_names;
	}

	public function return_empty_string() {
		return '';
	}
}
