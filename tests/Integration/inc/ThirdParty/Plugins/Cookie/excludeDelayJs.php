<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Cookie;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookies\Termly::clean_domain
 *
 * @group Termly
 */
class Test_ExcludeDelayJs extends TestCase {

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/Cookie/excludeDelayJs.php';

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		// TERMLY_VERSION is defined by tests/Integration/bootstrap.php only under the
		// dedicated Termly group (issue #8789 slice 2 gates Termly::is_activated() on it),
		// so the real container-registered subscriber is already attached to the event
		// manager here, matching the RocketLazyLoad/Perfmatters/etc. convention: apply the
		// filter directly rather than manually constructing and (de)registering a second,
		// duplicate subscriber instance.
		Functions\expect( 'get_option' )
			->twice()
			->andReturn( $config['termly_display_auto_blocker'] );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_delay_js_exclusions', $config['excluded'] )
		);

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_exclude_defer_js', $config['excluded'] )
		);
	}
}
