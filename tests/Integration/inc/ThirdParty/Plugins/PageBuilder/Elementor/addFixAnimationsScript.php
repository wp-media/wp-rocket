<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::add_fix_animation_script
 * @group Elementor
 * @group ThirdParty
 */
class Test_AddFixAnimationsScript extends TestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/addFixAnimationsScript.php';
	private $delay_js = false;

	public function setUp(): void {
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_fix_animation_script', 28 );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		$this->delay_js = false;
		$this->restoreWpFilter( 'rocket_buffer' );
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddFixScript( $config, $html, $expected ) {

		$this->delay_js            = $config['delay_js'];
		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $html )
		);
	}
	public function set_delay_js_option() {
		return $this->delay_js;
	}
}
