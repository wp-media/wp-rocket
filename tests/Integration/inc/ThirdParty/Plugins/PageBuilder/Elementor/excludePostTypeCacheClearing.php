<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_type_cache_clearing
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostTypeCacheClearing extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_pre_clean_post', $config['allow_exclusion'], $config['post'] )
		);
	}
}
