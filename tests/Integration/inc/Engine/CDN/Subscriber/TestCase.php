<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	protected $cnames;
	protected $cdn_zone;
	protected $site_url;

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'site_url', [ $this, 'setSiteURL' ] );

		parent::tearDown();
	}

	public function setCnames() {
		return $this->cnames;
	}

	public function setCDNZone() {
		return $this->cdn_zone;
	}

	public function setSiteURL() {
		return $this->site_url;
	}
}
