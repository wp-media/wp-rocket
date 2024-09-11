<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

use WP_Rocket\ThirdParty\Themes\Avada;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Avada::exclude_delay_js
 *
 * @group Themes
 */
class Test_ExcludeDelayJs extends TestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Themes/Avada/excludeDelayJs.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $exclusions, $expected ) {
		$this->subscriber = new Avada( $this->container->get( 'options' ) );

		$this->event->add_subscriber( $this->subscriber );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_wc_product_gallery_delay_js_exclusions', $exclusions )
		);
	}
}
