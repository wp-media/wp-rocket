<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class for DataManager::has_required_metrics()
 */
class HasRequiredMetricsTest extends TestCase {
	private $api_client_mock;
	private $options_mock;
	private $global_score_mock;
	private $data_manager;

	public function set_up() {
		parent::set_up();
		$this->api_client_mock   = Mockery::mock( APIClient::class );
		$this->options_mock      = Mockery::mock( Options_Data::class );
		$this->global_score_mock = Mockery::mock( GlobalScore::class );
		$this->data_manager      = new DataManager(
			$this->api_client_mock,
			$this->options_mock,
			$this->global_score_mock
		);
		$this->data_manager->set_logger( new Logger() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldCheckRequiredMetrics( $config, $expected ) {
		$this->global_score_mock->shouldReceive( 'get_global_score_data' )
			->once()
			->andReturn( $config['global_score_data'] );

		$result = $this->data_manager->has_required_metrics();

		$this->assertSame( $expected['has_metrics'], $result );
	}
}
