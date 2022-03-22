<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\JudgeMeWooCommerceSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\JudgeMeWooCommerceSubscriber;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\JudgeMeWooCommerceSubscriber::show_not_empty_product_gallery_with_delayJS
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_ShowNotEmptyProductGalleryWithDelayJS extends TestCase
{
	private $subscriber;

	public function setUp(): void
	{
		parent::setUp();
		$this->subscriber = new JudgeMeWooCommerceSubscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertEquals($expected['excluded'], $this->subscriber->show_not_empty_product_gallery_with_delayJS
		($config['excluded']));
	}
}
