<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::add_fix_animation_script
 * @group Elementor
 * @group ThirdParty
 */
class Test_AddFixAnimationsScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/addFixAnimationsScript.php';

	private $subscriber;
	private $delay_js_html;

	public function setUp() : void {
		parent::setUp();
		$this->delay_js_html = Mockery::mock( HTML::class );
		$this->subscriber = new Elementor( Mockery::mock( Options_Data::class ), $this->filesystem , $this->delay_js_html, Mockery::mock( UsedCSS::class ) );
	}

	public function tearDown() : void {
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddFixScript( $config , $html, $expected ) {
		$this->delay_js_html->shouldReceive( 'is_allowed' )
		              ->once()
		              ->andReturn( $config[ 'delay_js' ] );
		$this->assertSame(
			$expected,
			$this->subscriber->add_fix_animation_script( $html )
		);
	}
}
