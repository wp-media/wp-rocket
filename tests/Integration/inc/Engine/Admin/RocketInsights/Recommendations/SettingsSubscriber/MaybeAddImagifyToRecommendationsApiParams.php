<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class for SettingsSubscriber::maybe_add_imagify_to_recommendations_api_params()
 *
 * Note: This test only covers early return scenarios (when Imagify is not active or white label is disabled).
 * Full integration testing with Imagify active requires the actual Imagify plugin to be installed.
 *
 * @group RocketInsights
 * @group AdminOnly
 */
class Test_MaybeAddImagifyToRecommendationsApiParams extends TestCase {

	public function set_up() {
		parent::set_up();

		 $this->unregisterAllCallbacksExcept( 'rocket_insights_api_recommendations_params', 'maybe_add_imagify_to_recommendations_api_params', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_insights_api_recommendations_params' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Set up white label constant.
		$this->white_label = $config['white_label_account'];

		$this->assertSame( 
			$expected['params'], 
			wpm_apply_filters_typed( 'array', 'rocket_insights_api_recommendations_params', $config['params'] ) 
		);
	}
}
