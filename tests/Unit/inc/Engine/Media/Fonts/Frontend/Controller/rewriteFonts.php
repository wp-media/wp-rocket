<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Frontend\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\Fonts\Controller\Fonts;
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
	private $font;

	public function set_up() {
		parent::set_up();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->context    = Mockery::mock( Context::class );
		$this->font       = Mockery::mock( Fonts::class );
		$this->controller = new Controller( $this->context, $this->font );

		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $original, $expected ) {
		$this->context->shouldReceive('is_allowed')
			->once()
			->andReturn( $config['is_allowed'] );

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_CACHE_URL' )
			->once()
			->andReturn( $config['base_url'] );

		$this->font->shouldReceive('process')
			->atLeast()
			->times( $config['download_font'] )
			->andReturn( true );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->controller->rewrite_fonts( $original ) )
		);
	}
}
