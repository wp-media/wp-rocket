<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\ImagesDimensions\ImagesDimensions;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\ImagesDimensions\ImagesDimensions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImagesDimensions\ImagesDimensions::add_option
 *
 * @group  ImagesDimensions
 * @group  Media
 */
class Test_AddOption extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/ImagesDimensions/ImagesDimensions/addOption.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options    = isset( $input['options'] )  ? $input['options']  : [];
		$dimensions = new ImagesDimensions( Mockery::mock( Options_Data::class ), $this->filesystem );

		$this->assertSame(
			$expected,
			$dimensions->add_option( $options )
		);
	}
}
