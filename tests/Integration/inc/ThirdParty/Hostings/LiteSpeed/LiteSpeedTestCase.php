<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\LiteSpeed;

use HeaderCollector;
use WP_Rocket\Tests\Unit\TestCase;


abstract class LiteSpeedTestCase extends TestCase {

	public function setUp(): void {
		parent::setUp();
		HeaderCollector::clean();
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

}

