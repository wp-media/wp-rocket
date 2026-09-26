<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WP_Rocket\Tests\Integration\ResetsCdnDriverStateTrait;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use ResetsCdnDriverStateTrait;

	protected $cnames;
	protected $cdn_zone;
	protected $home_url;
	protected $content_url;
	protected $includes_url;

	/**
	 * Value returned by the pre_get_rocket_option_cdn_type filter added below.
	 *
	 * DriverFactory now resolves the active driver from Context::get_effective_cdn_state(),
	 * which needs cdn_type === 'byocdn' to route to the Custom (CNAME) driver these tests
	 * exercise — the default 'rocketcdn' value would otherwise route to the Disabled driver
	 * and silently stop every rewrite these tests assert on.
	 *
	 * @var string
	 */
	protected $cdn_type = 'byocdn';

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'setCdnType' ] );

		// Also reset before the test runs, in case an earlier, unrelated test file resolved
		// these same container singletons under a different cdn_type first.
		$this->reset_cdn_driver_memo();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'setCdnType' ] );
		remove_filter( 'home_url', [ $this, 'setHomeURL' ] );

		$this->reset_cdn_driver_memo();

		parent::tear_down();
	}

	public function setCdnType() {
		return $this->cdn_type;
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
