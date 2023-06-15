<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_post_type_cache_clearing
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludePostTypeCacheClearing extends TestCase {
	private $elementor;

	public function setUp(): void {
		parent::setUp();

		$this->elementor = new Elementor( Mockery::mock( Options_Data::class ), null, Mockery::mock( HTML::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame(
			$expected,
			$this->elementor->exclude_post_type_cache_clearing( $config['allow_exclusion'], $config['post_type'] )
		);
	}
}
