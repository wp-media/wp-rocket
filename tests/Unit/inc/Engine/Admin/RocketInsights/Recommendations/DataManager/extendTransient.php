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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::extend_transient
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_ExtendTransient extends TestCase {
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
		Functions\expect( 'get_transient' )
			->with( 'wpr_ri_recommendations' )
			->andReturn( $config['transient_data'] );

		if ( false !== $config['transient_data'] && ( ! isset( $config['transient_data']['status'] ) || ! isset( $config['transient_data']['timestamp'] ) ) ) {
			Functions\expect( 'delete_transient' )
				->with( 'wpr_ri_recommendations' )
				->once();
		}

		if ( $expected['extends_transient'] ) {
			Functions\expect( 'set_transient' )
				->with( 'wpr_ri_recommendations', Mockery::type( 'array' ), 86400 )
				->once();
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->data_manager->extend_transient();
	}
}
