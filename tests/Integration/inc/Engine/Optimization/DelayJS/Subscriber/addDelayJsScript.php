<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Subscriber::add_delay_js_script
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_AddDelayJsScript extends TestCase {

	private $delay_js = false;

	public function tearDown() {
		unset( $GLOBALS['wp'] );
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

		$this->delay_js = false;

		wp_dequeue_script('rocket-browser-checker');
		wp_dequeue_script('rocket-delay-js');

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldProcessScriptHTML( $config, $expected ) {
		$bypass         = isset( $config['bypass'] ) ? $config['bypass'] : false;
		$this->delay_js = isset( $config['delay_js'] ) ? $config['delay_js'] : false;

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

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

		do_action( 'wp_enqueue_scripts' );

		if ( false === $expected ) {
			$this->assertFalse( wp_script_is( 'rocket-browser-checker' ) );
			$this->assertFalse( wp_script_is( 'rocket-delay-js' ) );
		} else {
			$this->assertTrue( wp_script_is( 'rocket-browser-checker' ) );
			$this->assertTrue( wp_script_is( 'rocket-delay-js' ) );
		}

	}

	public function set_delay_js_option() {
		return $this->delay_js;
	}

}
