<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::add_fix_animation_script
 * @group Elementor
 * @group ThirdParty
 */
class Test_AddFixAnimationsScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/addFixAnimationsScript.php';
	private $delay_js = false;

	protected $js_version;

	public function set_up() {
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_fix_animation_script', 28 );
		add_filter( 'rocket_delay_js_version_js_script', [ $this, 'set_js_version' ] );
	}

	public function tear_down() {
		remove_filter( 'rocket_delay_js_version_js_script', [ $this, 'set_js_version' ] );
		remove_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );
		$this->delay_js = false;
		$this->restoreWpHook( 'rocket_buffer' );
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddFixScript( $config, $html, $expected ) {

		$this->delay_js            = $config['delay_js'];
		$this->js_version          = $config['js_version'];
		add_filter( 'pre_get_rocket_option_delay_js', [ $this, 'set_delay_js_option' ] );

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
