<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Ecommerce\WooCommerce;

use WP_Rocket\Subscriber\Third_Party\Plugins\Ecommerce\WooCommerce_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

class TestNonceUserLoggedOut extends TestCase
{
	/**
	 * Test should keep the original $uid.
	 *
	 * @since 3.5.1
	 * @author Soponar Cristina
	 *
	 */
	public function testShouldKeepTheOriginalUID()
	{
		$subscriber  = new WooCommerce_Subscriber();

		$uid    = 1;
		$action = 'security';
		$this->assertSame( $uid, $subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );
	}

	/**
	 * Test should keep the original $uid when $uid = 0.
	 *
	 * @since 3.5.1
	 * @author Soponar Cristina
	 *
	 */
	public function testShouldKeepTheOriginalUIDWithZero()
	{
		$subscriber  = new WooCommerce_Subscriber();

		$uid    = 0;
		$action = 'security';
		$this->assertSame( $uid, $subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );
	}

	/**
	 * Test should keep the original $uid when $uid = 0.
	 *
	 * @since 3.5.1
	 * @author Soponar Cristina
	 *
	 */
	public function testShouldKeepTheOriginalUIDWithEmptyAction()
	{
		$subscriber  = new WooCommerce_Subscriber();

		$uid    = 1;
		$action = '';
		$this->assertSame( $uid, $subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );
	}

	/**
	 * Test should change $uid to 0 when action in list.
	 *
	 * @since 3.5.1
	 * @author Soponar Cristina
	 *
	 */
	public function testShouldChangeUIDToZero()
	{
		$subscriber  = new WooCommerce_Subscriber();

		$uid    = 1;
		$action = 'wcmd-subscribe-secret';
		$this->assertSame( 0, $subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );

		$action = 'td-block';
		$this->assertSame( 0, $subscriber->maybe_revert_uid_for_nonce_actions( $uid, $action ) );
	}
}
