<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

abstract class GodaddyTestCase extends TestCase {
	use HttpRequestTrait;

	public function set_up() {
		parent::set_up();
		$this->setup_http();
	}

	public function tear_down() {
		$this->tear_down_http();
		parent::tear_down();
	}
}
