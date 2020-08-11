<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Optimization\DelayJS\Subscriber;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Subscriber::delay_js
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_DelayJs extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/DelayJS/Subscriber/delayJs.php';

	private $options;

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldProcessScriptHTML( $config, $expected ) {

		$allowed_scripts = isset( $config['allowed-scripts'] ) ? $config['allowed-scripts'] : [];

		$this->donotrocketoptimize       = isset( $config['do-not-optimize'] )    ? $config['do-not-optimize']    : false;
		$this->constants['donotdelayjs'] = isset( $config['do-not-delay-const'] ) ? $config['do-not-delay-const'] : false;

		$this->options->shouldReceive( 'get' )
			->with( 'delay_js' )
			->zeroOrMoreTimes()
			->andReturn( $config['do-not-delay-setting'] );

		$this->options->shouldReceive( 'get' )
			->with( 'delay_js_scripts', [] )
			->once()
			->andReturn( $allowed_scripts );


		$html           = new HTML( $this->options );
		$subscriber     = new Subscriber( $html, $this->filesystem );
		$processed_html = $subscriber->delay_js( $config['html'] );

		$this->assertSame( $expected['html'], $processed_html );
	}

}
