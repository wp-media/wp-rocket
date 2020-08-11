<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\HTML;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS::delay_js
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
	 * @dataProvider configTestData
	 */
	public function testShouldProcessScriptHTML( $config, $expected ) {

		$allowed_scripts = isset( $config['allowed-scripts'] ) ? $config['allowed-scripts'] : [];

		$this->donotrocketoptimize       = $config['do-not-optimize'];
		$this->constants['donotdelayjs'] = $config['do-not-delay-const'];

		$this->options->shouldReceive( 'get' )
			->with( 'delay_js' )
			->zeroOrMoreTimes()
			->andReturn( $config['do-not-delay-setting'] );

		$this->options->shouldReceive( 'get' )
			->with( 'delay_js_scripts', [] )
			->once()
			->andReturn( $allowed_scripts );


		$html           = new HTML( $this->options );
		$processed_html = $html->delay_js( $config['html'] );

		$this->assertSame( $expected['html'], $processed_html );
	}

}
