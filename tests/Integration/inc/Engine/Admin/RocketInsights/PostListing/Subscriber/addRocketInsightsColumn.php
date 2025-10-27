<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\PostListing\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\PostListing\Subscriber::add_rocket_insights_column
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_AddRocketInsightsColumn extends AdminTestCase {
	
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
		$container = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'ri_post_listing_subscriber' );
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
		// Call the method directly instead of relying on filter subscription.
		$columns = $this->subscriber->add_rocket_insights_column( $config['columns'] );

		$this->assertArrayHasKey( 'rocket_insights', $columns, 'Rocket Insights column should be present' );
		$this->assertSame( $expected['column_label'], $columns['rocket_insights'], 'Column label should match' );
	}
}
