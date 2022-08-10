<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::disable_post_type_archive
 * @group WooCommerce
 * @group ThirdParty
 * @group WithWoo
 */
class Test_DisablePostTypeArchive extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame($expected, apply_filters('rocket_post_type_archive_enable', $config['enabled'], $config['post']));
	}
}
