<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_js
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeJs extends TestCase {
	private $combine_js = false;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_combine_js' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->combine_js = $config['combine_js'];

		add_filter( 'pre_get_rocket_option_minify_concatenate_js', [ $this, 'set_combine_js' ] );

		$actual = apply_filters( 'rocket_exclude_js', [] );
		if ( empty( $expected ) ) {
			$this->assertEmpty( $expected ); // Todo: Need to be enhanced.
		}
		foreach ( $expected as $item ) {
			$this->assertContains( $item, $actual );
		}
	}

	public function set_combine_js() {
		return $this->combine_js;
	}
}
