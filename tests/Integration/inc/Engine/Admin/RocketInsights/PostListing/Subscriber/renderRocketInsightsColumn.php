<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::render_rocket_insights_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_RenderRocketInsightsColumn extends AdminTestCase {
	/**
	 * Test rendering of Rocket Insights column content.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldRenderPlaceholderContent( $config, $expected ) {
		// Create a test post.
		$post_id = $this->factory->post->create( $config['post_data'] );

		// Start output buffering to capture the rendered content.
		ob_start();
        // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( "manage_{$config['post_type']}_posts_custom_column", $config['column_name'], $post_id );
		$output = ob_get_clean();

		if ( $expected['should_render'] ) {
			$this->assertStringContainsString( $expected['content'], $output, 'Should render placeholder content' );
		} else {
			$this->assertEmpty( $output, 'Should not render content for other columns' );
		}
	}

	/**
	 * Test that no content is rendered when post has no permalink.
	 *
	 * @return void
	 */
	public function testShouldNotRenderWhenNoPermalink() {
		// Create a post but filter get_permalink to return false.
		$post_id = $this->factory->post->create( [ 'post_status' => 'draft' ] );

		add_filter( 'post_link', '__return_false' );

		ob_start();
        // @phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		do_action( 'manage_post_posts_custom_column', 'rocket_insights', $post_id );
		$output = ob_get_clean();

		remove_filter( 'post_link', '__return_false' );

		$this->assertEmpty( $output, 'Should not render when permalink is not available' );
	}
}
