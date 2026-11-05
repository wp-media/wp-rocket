<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\AdminBar\Subscriber;

use WP_Rocket\Engine\Admin\AdminBar\Subscriber;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\AdminBar\Subscriber::enqueue_admin_bar_style
 *
 * @group AdminBar
 */
class Test_EnqueueAdminBarStyle extends TestCase {
	private $user_id;

	public function tear_down() {
		wp_dequeue_style( 'wpr-admin-bar' );
		wp_deregister_style( 'wpr-admin-bar' );
		wp_set_current_user( 0 );
		set_current_screen( 'front' );
		remove_all_filters( 'show_admin_bar' );

		if ( $this->user_id ) {
			wp_delete_user( $this->user_id );
		}

		parent::tear_down();
	}

	private function find_registered_callback( string $hook ): bool {
		global $wp_filter;

		foreach ( $wp_filter[ $hook ]->callbacks as $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if (
					is_array( $callback['function'] )
					&& $callback['function'][0] instanceof Subscriber
					&& 'enqueue_admin_bar_style' === $callback['function'][1]
				) {
					return true;
				}
			}
		}

		return false;
	}

	public function testShouldBeRegisteredOnTheCommonPath() {
		$this->assertTrue( $this->find_registered_callback( 'wp_enqueue_scripts' ) );
		$this->assertTrue( $this->find_registered_callback( 'admin_enqueue_scripts' ) );
	}

	public function testShouldBeRegisteredInAdminRequest() {
		set_current_screen( 'dashboard' );

		$this->assertTrue( is_admin() );
		$this->assertTrue( $this->find_registered_callback( 'admin_enqueue_scripts' ) );
	}

	public function testShouldEnqueueOnFrontEndForPrivilegedUser() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		get_userdata( $this->user_id )->add_cap( 'rocket_manage_options' );
		add_filter( 'show_admin_bar', '__return_true' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
		$this->assertSame( [ 'admin-bar' ], wp_styles()->registered['wpr-admin-bar']->deps );
	}

	public function testShouldEnqueueInAdminForPrivilegedUser() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		get_userdata( $this->user_id )->add_cap( 'rocket_manage_options' );
		set_current_screen( 'dashboard' );

		do_action( 'admin_enqueue_scripts', 'index.php' );

		$this->assertTrue( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
	}

	public function testShouldNotEnqueueWhenLoggedOut() {
		add_filter( 'show_admin_bar', '__return_true' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
	}

	public function testShouldNotEnqueueForUnprivilegedUser() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $this->user_id );
		add_filter( 'show_admin_bar', '__return_true' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
	}

	public function testShouldNotEnqueueWhenAdminBarDisabled() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		get_userdata( $this->user_id )->add_cap( 'rocket_manage_options' );
		add_filter( 'show_admin_bar', '__return_false' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
	}
}
