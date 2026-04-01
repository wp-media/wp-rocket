<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

/**
 * Test class for SettingsSubscriber::maybe_add_imagify_to_recommendations_api_params()
 *
 * Note: This test only covers early return scenarios (when Imagify is not active or white label is disabled).
 * Full integration testing with Imagify active requires the actual Imagify plugin to be installed.
 *
 * @group RocketInsights
 * @group Recommendations
 * @group AdminOnly
 */
class MaybeAddImagifyToRecommendationsApiParamsTest extends TestCase {
	private $subscriber;

	public function set_up() {
		parent::set_up();

		// Get the SettingsSubscriber from the container.
		$container = apply_filters( 'rocket_container', null );
		if ( $container && $container->has( 'ri_recommendations_settings_subscriber' ) ) {
			$this->subscriber = $container->get( 'ri_recommendations_settings_subscriber' );
		}
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHandleImagifyApiParams( $config, $expected ) {
		// Set up white label constant.
		$this->white_label = $config['white_label_account'];

		// Test by calling the method directly.
		if ( $this->subscriber && method_exists( $this->subscriber, 'maybe_add_imagify_to_recommendations_api_params' ) ) {
			$result = $this->subscriber->maybe_add_imagify_to_recommendations_api_params( $config['params'] );
			$this->assertSame( $expected['params'], $result );
		} else {
			$this->markTestIncomplete( 'SettingsSubscriber not available in container.' );
		}
	}
}
