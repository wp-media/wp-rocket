<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	protected $cnames;
	protected $cdn_zone;
	protected $home_url;

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'home_url', [ $this, 'setHomeURL' ] );

		parent::tearDown();
	}

	public function setCnames() {
		return $this->cnames;
	}

	public function setCDNZone() {
		return $this->cdn_zone;
	}

	public function setHomeURL() {
		return $this->home_url;
	}
}
