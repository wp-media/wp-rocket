<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected $cnames;
	protected $cdn_zone;
	protected $home_url;
	protected $content_url;
	protected $includes_url;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'home_url', [ $this, 'setHomeURL' ] );

		parent::tear_down();
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

	public function setContentURL() {
		return $this->content_url;
	}

	public function setIncludesURL() {
		return $this->includes_url;
	}
}
