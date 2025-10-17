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
		$columns = apply_filters( "manage_{$config['post_type']}_posts_columns", $config['columns'] );

		$this->assertArrayHasKey( 'rocket_insights', $columns, 'Rocket Insights column should be present' );
		$this->assertSame( $expected['column_label'], $columns['rocket_insights'], 'Column label should match' );
	}
}
