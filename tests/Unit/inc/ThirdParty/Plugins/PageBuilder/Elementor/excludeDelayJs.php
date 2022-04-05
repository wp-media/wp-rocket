<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_delay_js
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeDelayJs extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/PageBuilder/Elementor/excludeDelayJs.php';
	protected $subscriber;

	public function setUp() : void {
		parent::setUp();
		$this->subscriber = new Elementor( Mockery::mock( Options_Data::class ), $this->filesystem , Mockery::mock( HTML::class ));
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldExcludeJs($config, $expected) {
		$this->assertEquals($expected, $this->subscriber->exclude_delay_js($config['excluded']));
	}
}
