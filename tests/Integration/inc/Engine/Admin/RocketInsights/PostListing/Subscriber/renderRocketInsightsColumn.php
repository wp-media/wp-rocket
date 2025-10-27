<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::render_rocket_insights_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_RenderRocketInsightsColumn extends AdminTestCase {
	use DBTrait;
	public static function set_up_before_class() {
		parent::set_up_before_class();

		// Install the Performance Monitoring table.
		self::installPerformanceMonitoringTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPerformanceMonitoringTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected) {
		$post_id = null;

		foreach ( $config['rows'] as $row ) {
			if ( ! empty($row)) {
				$this->addPerformanceMonitoring( $row );
			}
			
			// Create post if row is not empty OR if explicitly requested
			if ( ! empty($row) || ( isset($config['create_post']) && $config['create_post'] ) ) {
				$url = ! empty($row) ? $row['url'] : 'https://example.com/test-page';
				$post_id = $this->factory->post->create( [
					'post_title' => 'Test Post',
					'post_content' => 'Content',
					'post_status' => 'publish',
					'post_type' => 'post',
					'post_name' => 'page-to-test',
					'meta_input' => [ '_rocket_insights_url' => $url ]
				] );

				// Ensure the permalink matches our test URL
				add_filter( 'post_link', function( $permalink, $post ) use ( $post_id, $url ) {
					if ( $post->ID === $post_id ) {
						return $url;
					}
					return $permalink;
				}, 10, 2 );
			}
		}

		if ( null === $post_id ) {
			// Test case with no post
			ob_start();
			do_action( 'manage_post_posts_custom_column', 'rocket_insights', 99999 );
			$output = ob_get_clean();
			$this->assertSame( $expected['html'], $output );
			return;
		}

		ob_start();
		do_action( 'manage_post_posts_custom_column', 'rocket_insights', $post_id );
		$output = ob_get_clean();

		$this->assertStringContainsString( $expected['html'], $output );
	}
}
