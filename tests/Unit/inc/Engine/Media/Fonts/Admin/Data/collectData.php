<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Admin\Data;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Fonts\Admin\Data;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering  \WP_Rocket\Engine\Media\Fonts\Admin\Data::collect_data
 * @group  HostFontsLocally
 */
class Test_CollectData extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Admin/Data/collectData.php';

	private $options;
	private $data;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->options = Mockery::mock( Options_Data::class );
		$this->data    = new Data( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'host_fonts_locally', 0 )
			->andReturn( $config['options']['host_fonts_locally'] );

		$this->options->shouldReceive( 'get')
			->with( 'analytics_enabled', 0 )
			->andReturn( $config['options']['analytics_enabled'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );

		Functions\when( 'size_format' )->justReturn( '1.2 MB' );

		if ( $expected ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_fonts_data_collection', $expected, WEEK_IN_SECONDS );
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->data->collect_data();
	}
}
