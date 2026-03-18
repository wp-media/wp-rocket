<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\Subscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\{
	DataManager,
	Render,
	Subscriber
};
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Subscriber::handle_status_change
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
	 * Subscriber instance.
	 *
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->data_manager = Mockery::mock( DataManager::class );
		$this->subscriber   = new Subscriber( Mockery::mock( Render::class ), Mockery::mock( Context::class ), $this->data_manager );
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
			$this->data_manager->shouldReceive( 'maybe_fetch_recommendations' )
				->once()
				->andReturn( 'loading' !== $config['status'] && $config['has_required_metrics'] && $config['should_fetch'] );
		} else {
			$this->data_manager->shouldReceive( 'get_status' )->never();
			$this->data_manager->shouldReceive( 'has_required_metrics' )->never();
			$this->data_manager->shouldReceive( 'should_fetch_recommendations' )->never();
			$this->data_manager->shouldReceive( 'fetch_recommendations' )->never();
			$this->data_manager->shouldReceive( 'extend_transient' )->never();
		}

		if ( ! empty( $expected['failed_recommendations'] ) ){
			$this->data_manager->shouldReceive( 'set_recommendations_failed' )->once();
		}

		$this->subscriber->handle_status_change( $config['new_status'] );
	}
}
