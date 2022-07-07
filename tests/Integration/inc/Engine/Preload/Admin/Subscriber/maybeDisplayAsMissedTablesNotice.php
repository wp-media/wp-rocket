<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Admin\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

class Test_MaybeDisplayAsMissedTablesNotice extends AdminTestCase
{
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'maybeDisplayAsMissedTablesNotice' );
	}
}
