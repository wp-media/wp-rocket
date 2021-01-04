<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::clean_domain
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_MaybeDisableLazyload extends TestCase {
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Avada/maybeDisableLazyload.php';

	public function tearDown() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		remove_filter( 'pre_option_fusion_options', [ $this, 'set_fusion_options' ] );
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testMaybeDisableLayzloadWhenActiveInAvada( $fusion_options, $expected ) {
		$this->fusion_options = $fusion_options;

		add_filter( 'pre_option_fusion_options', [ $this, 'set_fusion_options' ] );

		$this->assertSame( $expected, apply_filters( 'rocket_maybe_disable_lazyload_helper', [] ) );
	}

	public function set_fusion_options() {
		return $this->fusion_options;
	}
}
