<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use Mockery;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::disable_post_type_archive
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_DisablePostTypeArchive extends TestCase
{
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new WooCommerceSubscriber( Mockery::mock( HTML::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->assertSame($expected, $this->subscriber->disable_post_type_archive($config['enabled'], $config['post']));
	}
}
