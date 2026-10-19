<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class for FrontendSubscriber::maybe_add_rocketcdn_to_recommendations_api_params()
 *
 * @group RocketInsights
 * @group RocketCDN
 * @group Recommendations
 */
class Test_MaybeAddRocketcdnToRecommendationsApiParams extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_insights_api_recommendations_params', 'maybe_add_rocketcdn_to_recommendations_api_params', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_insights_api_recommendations_params' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->white_label = $config['white_label'];

		$this->assertSame(
			$expected['params'],
			wpm_apply_filters_typed( 'array', 'rocket_insights_api_recommendations_params', $config['params'] )
		);
	}
}
