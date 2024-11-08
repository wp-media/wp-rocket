<?php
namespace WP_Rocket\tests\Integration\inc\Engine\Media\Fonts\Frontend\Controller;

use Mockery;
use WP_Rocket\Engine\Media\Fonts\Frontend\Controller;
use WP_Rocket\Engine\Media\Fonts\Context\Context;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;

/**
 * @group GoogleFontHost
 */
class Test_RewriteFonts extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$context = Mockery::mock(Context::class);

		$context->shouldReceive('is_allowed')
			->once()
			->andReturn( $config['is_allowed'] );

		$controller = new Controller($context, new Combine(), new CombineV2());

		$result = $controller->rewrite_fonts( $config['html'] );

		$this->assertEquals( $expected, $result );
	}

}
