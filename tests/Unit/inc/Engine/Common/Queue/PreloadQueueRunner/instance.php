<?php

use WP_Rocket\Engine\Common\Queue\PreloadQueueRunner;
use WP_Rocket\Tests\Unit\TestCase;

class Test_instance extends TestCase {
	public function testShouldInstanciateTheSame() {
		$this->assertSame(PreloadQueueRunner::instance(), PreloadQueueRunner::instance());
	}
}
