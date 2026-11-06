<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber::maybe_clear_on_rocketcdn_paid
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_MaybeClearOnRocketcdnPaid extends TestCase {
	/**
	 * Data manager mock.
	 *
	 * @var DataManager|\Mockery\MockInterface
	 */
	private $data_manager;

	/**
	 * Subscriber under test.
	 *
	 * @var SettingsSubscriber
	 */
	private $subscriber;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		$this->data_manager = Mockery::mock( DataManager::class );
		$this->subscriber   = new SettingsSubscriber( $this->data_manager );
	}

	/**
	 * Test that the cache is cleared only on a transition into the paid state.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Whether the recommendations are expected to be cleared.
	 */
	public function testShouldClearOnlyOnTransitionToPaid( $config, $expected ) {
		$this->data_manager->shouldReceive( 'clear_recommendations' )->times( $expected ? 1 : 0 );
		$this->data_manager->shouldNotReceive( 'fetch_recommendations' );

		$this->subscriber->maybe_clear_on_rocketcdn_paid( $config['old_options'], $config['new_options'] );
	}
}
