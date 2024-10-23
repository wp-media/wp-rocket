<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Data;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Data::get_support_data
 *
 * @group Support
 */
class Test_GetSupportData extends TestCase {
	private $wp_version;

	public function set_up() {
		parent::set_up();

		global $wp_version;

		add_filter( 'rocket_above_the_fold_optimization', '__return_true' );
		add_filter( 'rocket_lrc_optimization', '__return_true' );

		$this->wp_version = $wp_version;
	}

	public function tear_down() {
		global $wp_version;

		remove_filter( 'rocket_above_the_fold_optimization', '__return_true' );
		remove_filter( 'rocket_lrc_optimization', '__return_true' );

		$wp_version = $this->wp_version;

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $options_array, $expected ) {
		global $wp_version;

		$this->rocket_version = '3.7.5';

		$data       = new Data( new Options_Data( $options_array ) );
		$wp_version = '5.5';

		activate_plugin( 'hello.php' );

		$this->assertSame(
			$expected,
			$data->get_support_data()
		);
	}
}
