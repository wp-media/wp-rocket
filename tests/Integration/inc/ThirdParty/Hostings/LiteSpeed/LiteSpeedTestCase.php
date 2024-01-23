<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

use HeaderCollector;
use WP_Rocket\Tests\Unit\TestCase;


abstract class LiteSpeedTestCase extends TestCase {

	public function set_up() {
		parent::set_up();
		HeaderCollector::clean();
	}

	protected function tear_down() {
		parent::tear_down();
	}

}

