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
class Test_RewriteFonts extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Media/Fonts/Frontend/Controller/rewriteFonts.php';
	private $context;
	private $combineV1;
	private $combineV2;


	public function set_up() {
		parent::set_up();
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

		$this->context = Mockery::mock( Context::class );

		$this->combineV1 = Mockery::mock( Combine::class );
		$this->combineV2 = Mockery::mock( CombineV2::class );
	}
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->context->shouldReceive('is_allowed')
			->once()
			->andReturn( $config['is_allowed'] );

		$controller = new Controller( $this->context, $this->combineV1, $this->combineV2 );
//		foreach ($config['files'] as $file) {
//			$directory = dirname($file['path']);
//			if (!is_dir($directory)) {
//				mkdir($directory, 0777, true);
//			}
//			file_put_contents($file['path'], '');
//		}

		$result = $controller->rewrite_fonts( $config['html'] );

		$this->assertEquals( $expected, $result );
	}

}
