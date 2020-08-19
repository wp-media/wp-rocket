<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\HTML;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\HTML::delay_js
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_DelayJs extends TestCase {
	private $options;

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldProcessScriptHTML( $config, $html, $expected ) {
		$allowed_scripts           = isset( $config['allowed-scripts'] ) ? $config['allowed-scripts'] : [];
		$this->donotrocketoptimize = isset( $config['donotoptimize'] ) ? $config['donotoptimize'] : false;

		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( $config['bypass'] );

		if ( $this->donotrocketoptimize || $config['bypass'] ) {
			$this->options->shouldReceive( 'get' )
				->with( 'delay_js', 0 )
				->never();

			$this->options->shouldReceive( 'get' )
				->with( 'delay_js_scripts', [] )
				->never();
		} else {
			$this->options->shouldReceive( 'get' )
				->with( 'delay_js', 0 )
				->once()
				->andReturn( $config['do-not-delay-setting'] );

			if ( $config['do-not-delay-setting'] ) {
				$this->options->shouldReceive( 'get' )
					->with( 'delay_js_scripts', [] )
					->once()
					->andReturn( $allowed_scripts );
			}

		}

		$delay_js_html = new HTML( $this->options );

		$this->assertSame(
			$expected,
			$delay_js_html->delay_js( $html )
		);
	}
}
