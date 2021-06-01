<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::add_plugins_incompatibility
 *
 * @group DelayJS
 * @group AdminOnly
 */
class Test_AddPluginsIncompatibility extends TestCase {
	private $delay_js;

	public function setUp() : void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_plugins_to_deactivate', 'add_plugins_incompatibility' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );
		$this->restoreWpFilter( 'rocket_plugins_to_deactivate' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $options, $plugins, $expected ) {
		$this->delay_js = $options['delay_js'];

		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_plugins_to_deactivate', $plugins )
		);
	}

	public function set_delay_js() {
		return $this->delay_js;
	}
}
