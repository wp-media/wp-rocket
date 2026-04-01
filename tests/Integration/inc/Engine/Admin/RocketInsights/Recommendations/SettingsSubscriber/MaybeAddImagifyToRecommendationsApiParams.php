<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Mockery;

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
	 * 
	 * @runInSeparateProcess
	 * Required because Mockery alias mocking creates a real class definition
	 * for \Imagify_Partner at runtime. If the class is already loaded in the current process,
	 * PHP will throw a fatal error for redeclaring it. Running in a separate process ensures
	 * a clean state where the class hasn't been defined yet.
	
	 * @preserveGlobalState disabled
	 * Prevents PHPUnit from copying the parent process's global
 	 * state (loaded classes, constants, globals) into the new process, which would defeat the
 	 * purpose of @runInSeparateProcess and cause the alias mock to fail.
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		// Set up white label constant.
		$this->white_label = $config['white_label_account'];

		 Mockery::mock('alias:\Imagify_Partner')
			->shouldReceive('has_imagify_api_key')
			->once()
			->andReturn($config['has_imagify_api_key']);

		$this->assertSame( 
			$expected['params'], 
			wpm_apply_filters_typed( 'array', 'rocket_insights_api_recommendations_params', $config['params'] ) 
		);
	}
}
