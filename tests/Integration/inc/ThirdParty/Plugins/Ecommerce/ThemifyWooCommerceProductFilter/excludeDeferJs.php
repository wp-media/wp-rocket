<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\ThemifyWooCommerceProductFilter;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\ThemifyWooCommerceProductFilter;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\inc\ThirdParty\Plugins\Ecommerce\ThemifyWooCommerceProductFilter::exclude_defer_js
 * @group ThirdParty
 */

class Test_ExcludeDeferJs extends TestCase {
    public function setUp() : void {
		parent::setUp();
	}

	/**
	 * @dataProvider configTestData
	 */

	public function testShouldExcludeDeferJs( $option, $tests, $expected ) {
		$exclude_defer_js = new ThemifyWooCommerceProductFilter();

        Functions\when( 'is_plugin_active' )->justReturn( $option );

		$result = $exclude_defer_js->exclude_defer_js( $tests );

        if( $option == 1 ){
            $this->assertContains( $expected[0], $result );
        }
	}
}
