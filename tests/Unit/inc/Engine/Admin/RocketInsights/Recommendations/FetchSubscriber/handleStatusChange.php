<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\FetchSubscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\FetchSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\FetchSubscriber::handle_status_change
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_HandleStatusChange extends TestCase {
	/**
	 * DataManager mock.
	 *
	 * @var Mockery\MockInterface|DataManager
	 */
	private $data_manager;

	/**
	 * FetchSubscriber instance.
	 *
	 * @var FetchSubscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->data_manager = Mockery::mock( DataManager::class );
		$this->subscriber   = new FetchSubscriber( $this->data_manager );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		if ( $expected['clears_recommendations'] ) {
			$this->data_manager->shouldReceive( 'clear_recommendations' )->once();
		} else {
			$this->data_manager->shouldReceive( 'clear_recommendations' )->never();
		}

		if ( $expected['fetches_recommendations'] ) {
			$this->data_manager->shouldReceive( 'get_status' )
				->once()
				->andReturn( $config['status'] );

			if ( 'loading' !== $config['status'] ) {
				$this->data_manager->shouldReceive( 'has_required_metrics' )
					->once()
					->andReturn( $config['has_required_metrics'] );

				if ( $config['has_required_metrics'] ) {
					$this->data_manager->shouldReceive( 'should_fetch_recommendations' )
						->once()
						->andReturn( $config['should_fetch'] );

					if ( $config['should_fetch'] ) {
						$this->data_manager->shouldReceive( 'fetch_recommendations' )->once();
					} else {
						$this->data_manager->shouldReceive( 'extend_transient' )->once();
					}
				}
			}
		} else {
			$this->data_manager->shouldReceive( 'get_status' )->never();
			$this->data_manager->shouldReceive( 'has_required_metrics' )->never();
			$this->data_manager->shouldReceive( 'should_fetch_recommendations' )->never();
			$this->data_manager->shouldReceive( 'fetch_recommendations' )->never();
			$this->data_manager->shouldReceive( 'extend_transient' )->never();
		}

		$this->subscriber->handle_status_change( $config['new_status'], $config['previous_status'] );
	}
}
