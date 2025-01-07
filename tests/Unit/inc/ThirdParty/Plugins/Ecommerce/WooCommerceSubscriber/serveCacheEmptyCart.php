<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Mockery;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::serve_cache_empty_cart
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_ServeCacheEmptyCart extends TestCase {
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new WooCommerceSubscriber( Mockery::mock( HTML::class ) );
	}

	public function tearDown() : void {
		parent::tearDown();

		unset( $_GET['wc-ajax'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( isset( $config['wc-ajax'] ) ) {
			$_GET['wc-ajax'] = $config['wc-ajax'];
		}

		Functions\when( 'rocket_bypass' )->justReturn( $config['bypass'] );

		if ( ! $expected ) {
			$this->assertNull( $this->subscriber->serve_cache_empty_cart() );
		}
	}
}
