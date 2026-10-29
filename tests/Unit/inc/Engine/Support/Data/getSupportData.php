<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Data;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Data::get_support_data
 *
 * @group Support
 */
class Test_GetSupportData extends TestCase {
	private Options_Data $options;
	private Data $data;

	public function set_up() {
		parent::set_up();

		$this->rocket_version = '3.7.5';
		$this->options        = Mockery::mock( Options_Data::class );
		$this->data           = new Data( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options->shouldReceive( 'get_options' )
			->once()
			->andReturn( $config );

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\expect( 'get_bloginfo' )
			->with( 'version' )
			->andReturn( '5.5' );

		Functions\when( 'wp_get_theme' )->alias( function() {
			return new \WP_Theme( 'default', 'wp-content/themes' );
		} );

		Functions\when( 'rocket_get_active_plugins' )
			->justReturn( [ 'Hello Dolly' ] );

		$this->assertSame( $expected, $this->data->get_support_data() );
	}
}
