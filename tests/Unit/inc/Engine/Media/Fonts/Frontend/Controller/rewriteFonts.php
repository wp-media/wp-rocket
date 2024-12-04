<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Frontend\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\Fonts\Filesystem as FontsFilesystem;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @group HostFontsLocally
 */
class Test_RewriteFonts extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Frontend/Controller/rewriteFonts.php';

	private $context;
	private $controller;
	private $fonts_filesystem;

	public function set_up() {
		parent::set_up();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->context          = Mockery::mock( Context::class );
		$this->fonts_filesystem = Mockery::mock( FontsFilesystem::class );
		$this->controller       = new Controller( $this->context, $this->fonts_filesystem );

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $original, $expected ) {
		$this->context->shouldReceive('is_allowed')
			->once()
			->andReturn( $config['is_allowed'] );

		$this->fonts_filesystem->shouldReceive( 'write_font_css' )
			->andReturn( $config['write'] );
		$this->fonts_filesystem->shouldReceive( 'hash_to_path' )
			->andReturnUsing( function( $hash ) {
				$levels = wpm_apply_filters_typed('integer', 'rocket_used_css_dir_level', 3 );

				$base   = substr( $hash, 0, $levels );
				$remain = substr( $hash, $levels );

				$path_array   = str_split( $base );
				$path_array[] = $remain;

				return implode( '/', $path_array );
			} );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->controller->rewrite_fonts( $original ) )
		);
	}
}
