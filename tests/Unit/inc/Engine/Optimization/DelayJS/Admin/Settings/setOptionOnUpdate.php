<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::set_option_on_update
 *
 * @group  DelayJS
 */
class Test_SetOptionOnUpdate extends TestCase {

	protected $option;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $options, $old_version, $valid_version, $expected ) {
		$this->option = Mockery::mock(Options_Data::class);
		$settings = new Settings( Mockery::mock( SiteList::class), $this->option );

		if ( $valid_version ) {
			$this->stubWpParseUrl();
			$this->option->shouldReceive('get')->zeroOrMoreTimes()->andReturn($options);
			Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
			Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes' );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $expected )
				->once();
		} else {
			Functions\expect( 'update_option' )->never();
		}

		$settings->set_option_on_update( $old_version );
	}
}
