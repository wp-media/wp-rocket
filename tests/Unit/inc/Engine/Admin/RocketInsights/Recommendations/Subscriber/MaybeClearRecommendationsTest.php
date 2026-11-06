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
 * Test class for Subscriber::maybe_clear_recommendations()
 *
 * @group RocketInsights
 * @group Recommendations
 */
class MaybeClearRecommendationsTest extends TestCase {
	private $data_manager;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->data_manager = Mockery::mock( DataManager::class );
		$this->subscriber   = new Subscriber(
			Mockery::mock( Render::class ),
			Mockery::mock( Context::class ),
			$this->data_manager
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldClearRecommendationsAsExpected( $config, $expected ) {
		if ( $expected['should_clear'] ) {
			$this->data_manager->shouldReceive( 'clear_recommendations' )->once();
		} else {
			$this->data_manager->shouldNotReceive( 'clear_recommendations' );
		}

		$this->subscriber->maybe_clear_recommendations( $config['plugin'] );

		$this->addToAssertionCount( Mockery::getContainer()->mockery_getExpectationCount() );
	}
}
