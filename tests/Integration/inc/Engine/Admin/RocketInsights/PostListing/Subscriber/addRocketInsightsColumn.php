<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::add_rocket_insights_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_AddRocketInsightsColumn extends AdminTestCase {
	/**
	 * Container instance.
	 */
	private $container;

	/**
	 * Subscriber instance.
	 *
	 * @var \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber
	 */
	private $subscriber;
	
	/**
	 * Setup before tests.
	 */
	public function set_up() {
		parent::set_up();
		
		// Get the subscriber instance from container using the filter.
		$this->container = apply_filters( 'rocket_container', null );
		$this->subscriber = $this->container->get( 'ri_post_listing_subscriber' );

		// Enable Rocket Insights.
		add_filter( 'rocket_rocket_insights_enabled', '__return_true' );
	}

	public function tear_down() {
		// Remove Rocket Insights filter.
		remove_filter( 'rocket_rocket_insights_enabled', '__return_true' );

		parent::tear_down();
	}
	
	/**
	 * Test if Rocket Insights column is added to post listing columns.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldAddRocketInsightsColumn( $config, $expected ) {
		$this->container->get( 'user' )->set_user( $config['customer_data'] );
		Functions\when( 'wp_parse_url' )->justReturn( $config['is_live_site'] );

		// Call the method directly instead of relying on filter subscription.
		$columns = $this->subscriber->add_rocket_insights_column( $config['columns'] );

		if ( '' !== $expected['column_label'] ) {
			$this->assertArrayHasKey( 'rocket_insights', $columns, 'Rocket Insights column should be present' );
			$this->assertSame( $expected['column_label'], $columns['rocket_insights'], 'Column label should match' );

			return;
		}

		$this->assertArrayNotHasKey( 'rocket_insights', $columns, 'Rocket Insights column should not be present' );
	}
}
