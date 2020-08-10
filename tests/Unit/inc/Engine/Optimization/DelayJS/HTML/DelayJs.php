<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\HTML;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;

/**
 * @covers WP_Rocket\Engine\Optimization\DelayJS::delay_js
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_Optimize extends TestCase {

	private $options;

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldProcessScriptHTML( $config, $expected ) {
		Functions\when( 'rocket_get_constant' )->alias( function ( $const ) use ( $config ) {
			if ( 'DONOTROCKETOPTIMIZE' === $const ) {
				return $config['do-not-optimize'];
			} elseif ( 'DONOTDELAYJS' === $const ) {
				return $config['do-not-delay-const'];
			} else {
				return false;
			}
		}
		);

		$this->options->shouldReceive( 'get' )
			->zeroOrMoreTimes()
			->with( 'delay_js' )
			->andReturn( $config['do-not-delay-setting'] );

		$this->options->shouldReceive( 'get' )
			->once()
			->with( 'delay_js_scripts', [] )
			->andReturn( $config['allowed-scripts'] );


		$html           = new HTML( $this->options );
		$processed_html = $html->delay_js( $config['html'] );

		$this->assertSame( $expected['html'], $processed_html );
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'DelayJs' );
	}
}
