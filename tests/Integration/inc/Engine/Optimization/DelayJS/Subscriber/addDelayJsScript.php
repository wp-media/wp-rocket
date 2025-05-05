<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Subscriber::add_delay_js_script
 * @group  Optimize
 * @group  DelayJS
 *
 * @uses   rocket_get_constant()
 */
class Test_AddDelayJsScript extends TestCase {
	private $delay_js = false;

	protected $js_version;

	public function set_up() {
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_delay_js_script', 26 );
		add_filter( 'rocket_delay_js_version_js_script', [ $this, 'set_js_version' ] );
	}

	public function tear_down() {
		unset( $_GET['nowprocket'] );
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		remove_filter( 'rocket_delay_js_version_js_script', [ $this, 'set_js_version' ] );

		$this->delay_js = false;
		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotoptimize'];
		$this->delay_js            = $config['delay_js'];
		$this->js_version          = $config['js_version'];

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

		if ( $config['bypass'] ) {
			$_GET['nowprocket'] = 1;
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $html )
		);
	}

	public function set_delay_js_option() {
		return $this->delay_js;
	}

	public function set_js_version() {
		return $this->js_version;
	}

}
