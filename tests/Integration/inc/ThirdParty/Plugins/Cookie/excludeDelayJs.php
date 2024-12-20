<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Cookie;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookies\Termly::clean_domain
 *
 * @group Plugins
 */
class Test_ExcludeDelayJs extends TestCase {

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/Cookie/excludeDelayJs.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$termly = new Termly();

		Functions\expect( 'get_option' )
			->once()
			->andReturn( $config['termly_display_auto_blocker'] );

		$this->assertSame( $expected, $termly->exclude_termly_defer_and_delay_js( $config['excluded'] ) );
	}
}
