<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use ThirdParty\Plugins\PageBuilder\Elementor\ElementorTestTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_type_cache_clearing
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostTypeCacheClearing extends TestCase {
	use ElementorTestTrait;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			$this->elementor->exclude_post_type_cache_clearing( $config['allow_exclusion'], $config['post'] )
		);
	}
}
