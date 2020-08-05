<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::add_options
 *
 * @group  DelayJs
 */
class Test_AddOptions extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];

		$options_data = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options_data );
		$subscriber = new Subscriber( $settings, '' );
		$actual = $subscriber->add_options($options);

		$this->assertSame( $expected, $actual );

	}

}
