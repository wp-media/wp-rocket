<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\InlineRelatedPosts;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts::exclude_inline_from_rucss
 * @group InlineRelatedPosts
 * @group ThirdParty
 */
class Test_ExcludeInlineFromRucss extends TestCase {
	protected $inline_related_posts;

	protected function setUp(): void
	{
		parent::setUp();
		$this->inline_related_posts = new InlineRelatedPosts();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {

		$this->assertSame( $expected, $this->inline_related_posts->exclude_inline_from_rucss( $config['excluded'] ) );
	}
}
