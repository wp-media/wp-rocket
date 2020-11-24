<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Images\Subscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Images\Subscriber::specify_image_dimensions
 * @group  Media
 */
class Test_ImageDimensions extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Images/Frontend/specifyImageDimensions.php';

	public function setUp() {
		parent::setUp();
		$this->LS_ROOT_FILE = "ls/root/file";
	}

	public function tearDown() {
		unset( $GLOBALS['wp'] );
		unset($this->LS_ROOT_FILE);

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddMissedDimensions( $input, $config, $expected ) {
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org'
		];

		$this->assertSame(
			$this->format_the_html( $input ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $input ) )
		);

	}

}
