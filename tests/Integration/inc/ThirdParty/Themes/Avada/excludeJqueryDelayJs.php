<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

/**
 * @covers \WP_Rocket\ThirdParty\Avada::maybe_exclude_jquery_delay_js
 *
 * @group  AvadaTheme
 * @group  ThirdParty
 */
class Test_ExcludeJqueryDelayJs extends TestCase {
	protected      $path_to_test_data = '/inc/ThirdParty/Themes/Avada/excludeJqueryDelayJs.php';

	public function set_up() {
		parent::set_up();
		add_option( 'fusion_options', [ 'lazy_load' => 'avada' ] );
	}

	public function tear_down() {
		delete_option( 'fusion_options' );
		parent::tear_down();
	}


	public function testShouldReturnExpected() {
		$expected = [
			'/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js',
		];
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_delay_js', [] )
		);
	}
}
