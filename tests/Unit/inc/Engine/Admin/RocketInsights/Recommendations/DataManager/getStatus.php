<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::get_status
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_GetStatus extends TestCase {
	use HasLoggerTrait;

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

		$api_client   = Mockery::mock( APIClient::class );
		$options      = Mockery::mock( Options_Data::class );
		$global_score = Mockery::mock( GlobalScore::class );

		$this->data_manager = new DataManager( $api_client, $options, $global_score );
		$this->set_logger( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Mock get_transient
		Functions\expect( 'get_transient' )
			->with( 'wpr_ri_recommendations' )
			->andReturn( $config['transient_data'] );

		// Mock delete_transient if needed for invalid structure
		if ( isset( $config['transient_data']['status'] ) && empty( $config['transient_data']['timestamp'] ) ) {
			Functions\expect( 'delete_transient' )
				->with( 'wpr_ri_recommendations' )
				->once();
		}

		$result = $this->data_manager->get_status();

		$this->assertSame( $expected, $result );
	}
}
