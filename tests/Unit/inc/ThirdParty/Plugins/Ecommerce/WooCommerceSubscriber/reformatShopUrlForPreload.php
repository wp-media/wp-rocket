<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::reformat_shop_url_for_preload
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_ReformatShopUrlForPreload extends TestCase
{
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new WooCommerceSubscriber( Mockery::mock( HTML::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$GLOBALS['wp_rewrite'] = $config['rewrite'];
		Functions\when('is_ssl')->justReturn($config['is_ssl']);
		if($config['is_right_post']) {
			Functions\expect('get_post_type_archive_link')->with($config['post_type'])->andReturn($config['post_type_link']);
		}
		$this->assertSame($expected, $this->subscriber->reformat_shop_url_for_preload($config['urls'], $config['post']));
	}
}
