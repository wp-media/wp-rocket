<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\InlineRelatedPosts;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts::exclude_inline_from_rucss
 * @group   InlineRelatedPosts
 */
class Test_ExcludeInlineFromRucss extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $inline_related_posts = new InlineRelatedPosts();
		$this->assertSame( $expected, $inline_related_posts->exclude_inline_from_rucss($config['excluded'] ) );
	}

}
