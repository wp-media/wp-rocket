<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Subscriber::delay_js
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_DelayJs extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/DelayJS/Subscriber/delayJs.php';

	private $options_data = [];

	public function tearDown() {
		parent::tearDown();

		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		remove_filter( 'pre_get_rocket_option_delay_js_scripts', [ $this, 'set_delay_js_scripts_option' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldProcessScriptHTML( $config, $expected ) {
		$bypass                    = isset( $config['bypass'] ) ? $config['bypass'] : false;
		$this->donotrocketoptimize = isset( $config['do-not-optimize'] )    ? $config['do-not-optimize']    : false;

		$this->options_data        = [
			'delay_js'         => isset( $config['do-not-delay-setting'] ) ? $config['do-not-delay-setting'] : false,
			'delay_js_scripts' => isset( $config['allowed-scripts'] )      ? $config['allowed-scripts']      : []
		];

		add_filter( 'pre_get_rocket_option_delay_js'         , [ $this, 'set_delay_js_option' ] );
		add_filter( 'pre_get_rocket_option_delay_js_scripts' , [ $this, 'set_delay_js_scripts_option' ] );

		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
		];

		if ( $bypass ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = 1;
		}

		$actual = $this->getActualHtml( $config['html'] );

		$this->assertSame( $expected['html'], $actual );
	}

	public function set_delay_js_option() {
		return isset( $this->options_data[ 'delay_js' ] ) ? $this->options_data[ 'delay_js' ] : false;
	}

	public function set_delay_js_scripts_option() {
		return isset( $this->options_data[ 'delay_js_scripts' ] ) ? $this->options_data[ 'delay_js_scripts' ] : [];
	}

	private function getActualHtml( $html ) {
		return apply_filters( 'rocket_buffer', $html );
	}

}
