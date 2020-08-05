<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::display_restore_defaults_button
 *
 * @group  DelayJs
 */
class Test_DisplayRestoreDefaultsButton extends TestCase{
	protected static $mockCommonWpFunctionsInSetUp = true;

	public function testShouldDoExpected(){
		$options_data = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options_data );
		$subscriber = Mockery::mock( Subscriber::class . '[render_action_button]', [
			$settings, WP_ROCKET_PLUGIN_ROOT . 'views/settingss/'
		] );

		$subscriber->shouldReceive('render_action_button')->with(
			'button',
				'rocket_delay_js_restore_defaults',
				[
					'label'      => __( 'Restore Defaults', 'rocket' ),
					'attributes' => [
						'class' => 'wpr-button wpr-button--icon wpr-button--purple wpr-icon-refresh',
					],
				]
		)->once();

		$subscriber->display_restore_defaults_button();
	}

}
