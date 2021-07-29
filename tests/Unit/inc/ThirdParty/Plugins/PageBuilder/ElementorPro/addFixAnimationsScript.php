<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\ElementorPro;

use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\ElementorPro;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Tests\Unit\TestCase;
use Mockery;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\ElementorPro::add_fix_animation_script
 * @group ElementorPro
 * @group ThirdParty
 */
class Test_AddFixAnimationsScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/ElementorPro/addFixAnimationsScript.php';

	private $subscriber;

	public function setUp() : void {
		parent::setUp();
		$this->subscriber = new ElementorPro( $this->filesystem , Mockery::mock( HTML::class ) );
	}

	public function tearDown() : void {
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddFixScript( $html, $expected ) {
		$this->assertSame(
			$expected,
			$this->subscriber->add_fix_animation_script( $html )
		);
	}
}
