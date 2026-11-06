<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Admin\Subscriber::add_meta_box
 * @group AdminOnly
 */
class Test_AddMetaBox extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->assertArrayHasKey(
			$expected,
			apply_filters('rocket_meta_boxes_fields', $config['fields'] )
		);
	}
}
