<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Frontend\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\Fonts\Context\OptimizationContext;
use WP_Rocket\Engine\Media\Fonts\Context\SaasContext;
use WP_Rocket\Engine\Media\Fonts\Filesystem as FontsFilesystem;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\Fonts\Frontend\Controller::rewrite_fonts_in_head
 *
 * @group HostFontsLocally
 */
class Test_RewriteFontsInHead extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedItems( $config, $expected ) {
		$this->constants = [
			'WP_ROCKET_CACHE_ROOT_PATH' => 'vfs://public/wp-content/cache/',
			'WP_ROCKET_CACHE_ROOT_URL'  => 'http://example.org/wp-content/cache/',
		];

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		$this->stubWpParseUrl();

		$optimization_context = Mockery::mock( OptimizationContext::class );
		$fonts_filesystem     = Mockery::mock( FontsFilesystem::class );

		$optimization_context->shouldReceive( 'is_allowed' )
			->once()
			->andReturn( $config['is_allowed'] );

		$fonts_filesystem->shouldReceive( 'exists' )
			->andReturn( false );
		$fonts_filesystem->shouldReceive( 'hash_to_path' )
			->andReturnUsing( function ( $hash ) {
				$path_array   = str_split( substr( $hash, 0, 3 ) );
				$path_array[] = substr( $hash, 3 );

				return implode( '/', $path_array );
			} );

		if ( empty( $expected['fetched'] ) ) {
			$fonts_filesystem->shouldNotReceive( 'write_font_css' );
		}

		foreach ( $expected['fetched'] as $href ) {
			$fonts_filesystem->shouldReceive( 'write_font_css' )
				->once()
				->with( $href, 'google-fonts' )
				->andReturn( true );
		}

		$controller = new Controller( $optimization_context, Mockery::mock( SaasContext::class ), $fonts_filesystem );

		$this->assertSame( $expected['items'], $controller->rewrite_fonts_in_head( $config['items'] ) );
	}
}
