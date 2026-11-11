<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\ImageDimensions\ImageDimensions;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\ImageDimensions\ImageDimensions::add_option
 *
 * @group  ImageDimensions
 * @group  Media
 */
class Test_AddOption extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/ImageDimensions/ImageDimensions/addOption.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options    = isset( $input['options'] )  ? $input['options']  : [];
		$dimensions = new ImageDimensions( Mockery::mock( Options_Data::class ), $this->filesystem );

		$this->assertSame(
			$expected,
			$dimensions->add_option( $options )
		);
	}
}
