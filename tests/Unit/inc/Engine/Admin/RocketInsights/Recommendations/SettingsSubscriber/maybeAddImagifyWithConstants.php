<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber::maybe_add_imagify_to_recommendations_api_params
 *
 * Covers the constants-based conditions, which can't be set from integration tests without leaking into other tests.
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_MaybeAddImagifyWithConstants extends TestCase {

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

		$this->subscriber = new SettingsSubscriber( Mockery::mock( DataManager::class ) );
	}

	/**
	 * Test that the Imagify param is added when the constants say so.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected result.
	 */
	public function testShouldReturnExpectedParams( $config, $expected ) {
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $config ) {
				return $config['constants'][ $name ] ?? $default;
			}
		);

		$this->assertSame( $expected['params'], $this->subscriber->maybe_add_imagify_to_recommendations_api_params( $config['params'] ) );
	}
}
