<?php

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts::exclude_inline_from_rucss
 * @group   InlineRelatedPosts
 */
class Test_ExcludeInlineFromRucss extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_rucss_inline_content_exclusions', $config['excluded'] ));
	}

}
