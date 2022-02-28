<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_js
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeJs extends TestCase {
	private $combine_js = false;
	private $user_cache = false;

	public function setUp(): void {
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'add_fix_animation_script', 28 );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_combine_js' ] );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_user_cache' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->combine_js = $config['combine_js'];
		$this->user_cache = $config['user_cache'];

		add_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_combine_js' ] );
		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_user_cache' ] );

		if ( $config['logged_in'] ) {
			$user_id = $this->factory->user->create( [ 'role' => 'contributor' ] );
			wp_set_current_user( $user_id );
		}

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_js', [] )
		);
	}

	public function set_combine_js() {
		return $this->combine_js;
	}

	public function set_user_cache() {
		return $this->user_cache;
	}
}
