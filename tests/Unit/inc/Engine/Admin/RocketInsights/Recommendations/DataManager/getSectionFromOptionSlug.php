<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\MetricFormatter;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::get_section_from_option_slug
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_GetSectionFromOptionSlug extends TestCase {
	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->data_manager = new DataManager(
			Mockery::mock( APIClient::class ),
			Mockery::mock( Options_Data::class ),
			Mockery::mock( GlobalScore::class ),
			Mockery::mock( MetricFormatter::class ),
			$this->createMock( RocketInsightsQuery::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->assertSame( $expected, $this->data_manager->get_section_from_option_slug( $config['option_slug'] ) );
	}
}
