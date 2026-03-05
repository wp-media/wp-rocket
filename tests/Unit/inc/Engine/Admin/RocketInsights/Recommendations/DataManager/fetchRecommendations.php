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
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::fetch_recommendations
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_FetchRecommendations extends TestCase {
	use HasLoggerTrait;

	/**
	 * API Client mock.
	 *
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * Options mock.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * Global Score mock.
	 *
	 * @var Mockery\MockInterface|GlobalScore
	 */
	private $global_score;

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

		$this->api_client   = Mockery::mock( APIClient::class );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->global_score = Mockery::mock( GlobalScore::class );

		$this->data_manager = new DataManager( $this->api_client, $this->options, $this->global_score );
		$this->set_logger( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Mock constants
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_VERSION' )
			->andReturn( $config['version'] );

		// Mock get_locale
		Functions\expect( 'get_locale' )
			->andReturn( $config['locale'] );

		// Mock Options_Data::get
		$this->options->shouldReceive( 'get' )
			->andReturnUsing(
				function ( $key, $default ) use ( $config ) {
					return $config['options'][ $key ] ?? $default;
				}
			);

		// Mock GlobalScore::get_global_score_data
		$this->global_score->shouldReceive( 'get_global_score_data' )
			->andReturn( $config['global_score_data'] );

		// Mock API Client response
		$this->api_client->shouldReceive( 'get_recommendations' )
			->once()
			->with( Mockery::on(
				function ( $params ) use ( $config ) {
					return $params['email'] === $config['expected_params']['email'];
				}
			) )
			->andReturn( $config['api_response'] );

		// Mock transient functions
		Functions\expect( 'set_transient' )
			->times( $config['transient_set_times'] );

		Functions\expect( 'delete_transient' )->zeroOrMoreTimes();

		$result = $this->data_manager->fetch_recommendations();

		$this->assertSame( $expected['result'], $result );
	}
}
