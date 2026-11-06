<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::set_option_on_update
 *
 * @group  DelayJS
 */
class Test_SetOptionOnUpdate extends TestCase {

	protected $option;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $options, $old_version, $valid_version, $expected ) {
		$this->option = Mockery::mock(Options::class);
		$settings = new Settings( $this->option );

		if ( $valid_version ) {
			$this->stubWpParseUrl();
			$this->option->shouldReceive('get')->zeroOrMoreTimes()->andReturn($options);
			Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
			Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes' );
			$this->option->shouldReceive('set')->with( 'settings', $expected )->once();
		} else {
			$this->option->shouldReceive('set')->never();
		}

		$settings->set_option_on_update( $old_version );
	}
}
