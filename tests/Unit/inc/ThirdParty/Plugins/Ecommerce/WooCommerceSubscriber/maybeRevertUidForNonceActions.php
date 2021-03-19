<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::maybe_revert_uid_for_nonce_actions
 * @group WooCommerce
 * @group ThirdParty
 */
class Test_MaybeRevertUidForNonceActions extends TestCase {
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new WooCommerceSubscriber();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUID( $uid, $action, $expected ) {
		$this->assertSame( $expected, $this->subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'nonce' );
	}
}
