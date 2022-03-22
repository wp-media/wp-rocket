<?php

use WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber\WooTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\JudgeMeWooCommerceSubscriber::show_not_empty_product_gallery_with_delayJS
 * @group WooCommerce
 * @group ThirdParty
 * @group WithWoo
 */
class Test_ShowNotEmptyProductGalleryWithDelayJS extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertEquals($expected['excluded'], apply_filters( 'rocket_delay_js_exclusions', $config['excluded']));
	}
}
