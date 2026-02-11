<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::handle_rocketcdn_checkout_parameter
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_HandleRocketcdnCheckoutParameter extends TestCase {

	/**
	 * Original $_GET superglobal.
	 *
	 * @var array
	 */
	private $original_get;

	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Test setup, not processing form data.
		$this->original_get = $_GET;

		// Set up admin user with proper capabilities.
		$admin_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );
		$user = wp_get_current_user();
		$user->add_cap( 'rocket_manage_options' );

		// Clean state.
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'wp_rocket_customer_data' );

		// Get the subscriber from container.
		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @return void
	 */
	public function tear_down() {
		$_GET = $this->original_get;
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'wp_rocket_customer_data' );

		parent::tear_down();
	}

	/**
	 * Test should bail out when parameter not set.
	 *
	 * @return void
	 */
	public function testShouldBailOutWhenParameterNotSet() {
		unset( $_GET['rocketcdn_checkout'] );

		$this->subscriber->handle_rocketcdn_checkout_parameter();

		// No option should be set.
		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
	}

	/**
	 * Test should bail out when user lacks permission.
	 *
	 * @return void
	 */
	public function testShouldBailOutWhenUserLacksPermission() {
		$_GET['rocketcdn_checkout'] = 'true';

		// Switch to subscriber (no permissions).
		$subscriber_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber_id );

		$this->subscriber->handle_rocketcdn_checkout_parameter();

		// No option should be set.
		$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
	}

	/**
	 * Test should bail out and redirect when token already exists.
	 *
	 * @return void
	 */
	public function testShouldBailOutAndRedirectWhenTokenAlreadyExists() {
		$_GET['rocketcdn_checkout'] = 'true';

		// Pre-existing token.
		update_option( 'rocketcdn_user_token', 'existing_token_12345678901234567890' );

		$this->subscriber->handle_rocketcdn_checkout_parameter();

		// Token should remain unchanged.
		$this->assertSame( 'existing_token_12345678901234567890', get_option( 'rocketcdn_user_token' ) );
	}
}
