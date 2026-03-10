<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class for SettingsSubscriber::maybe_fetch_after_settings_change()
 */
class MaybeFetchAfterSettingsChangeTest extends TestCase {
	private $data_manager_mock;
	private $subscriber;

	public function set_up() {
		parent::set_up();
		$this->data_manager_mock = Mockery::mock( DataManager::class );
		$this->subscriber        = new SettingsSubscriber( $this->data_manager_mock );
		$this->subscriber->set_logger( new Logger() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHandleSettingsChange( $config, $expected ) {
		// Mock get_status.
		$this->data_manager_mock->shouldReceive( 'get_status' )
			->once()
			->andReturn( $config['status'] );

		// If status is not ready, should bail early without checking hash or fetching.
		if ( ! in_array( $config['status'], [ 'completed', 'failed' ], true ) ) {
			$this->data_manager_mock->shouldNotReceive( 'should_fetch_recommendations' );
			$this->data_manager_mock->shouldNotReceive( 'fetch_recommendations' );

			$this->subscriber->maybe_fetch_after_settings_change(
				$config['old_options'],
				$config['new_options']
			);

			$this->addToAssertionCount( Mockery::getContainer()->mockery_getExpectationCount() );
			return;
		}

		// If no relevant changes, should bail without checking hash or fetching.
		if ( ! $config['has_relevant_changes'] ) {
			$this->data_manager_mock->shouldNotReceive( 'should_fetch_recommendations' );
			$this->data_manager_mock->shouldNotReceive( 'fetch_recommendations' );

			$this->subscriber->maybe_fetch_after_settings_change(
				$config['old_options'],
				$config['new_options']
			);

			$this->addToAssertionCount( Mockery::getContainer()->mockery_getExpectationCount() );
			return;
		}

		// If hash hasn't changed, should not fetch.
		if ( ! $expected['should_fetch'] ) {
			$this->data_manager_mock->shouldReceive( 'should_fetch_recommendations' )
				->once()
				->andReturn( false );

			$this->data_manager_mock->shouldNotReceive( 'fetch_recommendations' );

			$this->subscriber->maybe_fetch_after_settings_change(
				$config['old_options'],
				$config['new_options']
			);

			$this->addToAssertionCount( Mockery::getContainer()->mockery_getExpectationCount() );
			return;
		}

		// Should fetch when all conditions met.
		$this->data_manager_mock->shouldReceive( 'should_fetch_recommendations' )
			->once()
			->andReturn( true );

		$this->data_manager_mock->shouldReceive( 'fetch_recommendations' )
			->once()
			->andReturn( true );

		$this->subscriber->maybe_fetch_after_settings_change(
			$config['old_options'],
			$config['new_options']
		);

		$this->addToAssertionCount( Mockery::getContainer()->mockery_getExpectationCount() );
	}
}
