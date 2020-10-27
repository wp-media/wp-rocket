<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Data;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Support\Data::get_support_data
 *
 * @group Support
 */
class Test_GetSupportData extends TestCase {
	private $wp_version;

	public function setUp() {
		parent::setUp();

		global $wp_version;

		$this->wp_version = $wp_version;
	}

	public function tearDown() {
		global $wp_version;

		$wp_version = $this->wp_version;

		parent::tearDown();
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
