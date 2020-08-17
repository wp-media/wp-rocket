<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::display_restore_defaults_button
 *
 * @group  DelayJS
 */
class Test_DisplayRestoreDefaultsButton extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	public function testShouldDoExpected() {
		$settings     = Mockery::mock( Settings::class );
		$subscriber   = Mockery::mock(
			Subscriber::class . '[render_action_button]',
			[
				$settings,
				WP_ROCKET_PLUGIN_ROOT . 'views/settings/'
			]
		);

		$settings->shouldReceive( 'get_button_data' )
			->once()
			->andReturn(
				[
					'type'       => 'button',
					'action'     => 'rocket_delay_js_restore_defaults',
					'attributes' => [
						'label'      => 'Restore Defaults',
						'attributes' => [
							'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
						],
					],
				]
			);
		$subscriber->shouldReceive( 'render_action_button' )
			->with(
				'button',
				'rocket_delay_js_restore_defaults',
				[
					'label'      => 'Restore Defaults',
					'attributes' => [
						'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
					],
				]
			)
			->once();

		$subscriber->display_restore_defaults_button();
	}
}
