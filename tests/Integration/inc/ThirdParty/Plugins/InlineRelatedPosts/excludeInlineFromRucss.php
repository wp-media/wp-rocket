<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\InlineRelatedPosts::exclude_inline_from_rucss
 * @group   InlineRelatedPosts
 */
class Test_ExcludeInlineFromRucss extends TestCase {

	public function tear_down() {
		remove_filter( 'rocket_rucss_inline_content_exclusions', [ $this, 'return_false' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
        $inline_related_posts = new InlineRelatedPosts();
		$this->assertSame( $expected, apply_filters( 'rocket_rucss_inline_content_exclusions', $inline_related_posts->exclude_inline_from_rucss($config['excluded'] ) ) );
	}

}
