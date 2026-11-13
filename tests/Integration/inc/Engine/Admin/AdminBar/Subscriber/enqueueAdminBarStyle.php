<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\AdminBar\Subscriber;

use WP_Rocket\Engine\Admin\AdminBar\Subscriber;
use WP_Rocket\Plugin;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\AdminBar\Subscriber::enqueue_admin_bar_style
 *
 * @group AdminBar
 */
class Test_EnqueueAdminBarStyle extends TestCase {
	private $user_id;

	/**
	 * Grants the administrator role the WP Rocket capability.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Grant on the role so users created afterwards inherit it, whatever the DB state.
		self::setAdminCap();
	}

	public function tear_down() {
		wp_dequeue_style( 'wpr-admin-bar' );
		wp_deregister_style( 'wpr-admin-bar' );
		wp_set_current_user( 0 );
		set_current_screen( 'front' );
		remove_all_filters( 'show_admin_bar' );

		if ( $this->user_id ) {
			wp_delete_user( $this->user_id );
		}

		self::resetAdminCap();

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

	/**
	 * Makes a reflection member accessible; needed on PHP < 8.1 and deprecated since 8.5.
	 *
	 * @param \ReflectionProperty|\ReflectionMethod $member Reflection member.
	 *
	 * @return void
	 */
	private function make_accessible( $member ): void {
		if ( PHP_VERSION_ID < 80100 ) {
			$member->setAccessible( true );
		}
	}

	public function testShouldBeRegisteredOnTheCommonPath() {
		$this->assertTrue( $this->find_registered_callback( 'wp_enqueue_scripts' ) );
		$this->assertTrue( $this->find_registered_callback( 'admin_enqueue_scripts' ) );
	}

	public function testShouldBeRegisteredWithAnInvalidKey() {
		set_current_screen( 'front' );

		$plugin = ( new \ReflectionClass( Plugin::class ) )->newInstanceWithoutConstructor();

		$container = new \ReflectionProperty( Plugin::class, 'container' );
		$this->make_accessible( $container );
		$container->setValue( $plugin, apply_filters( 'rocket_container', null ) );

		$options = new \ReflectionProperty( Plugin::class, 'options' );
		$this->make_accessible( $options );
		$options->setValue( $plugin, apply_filters( 'rocket_container', null )->get( 'options' ) );

		$is_valid_key = new \ReflectionProperty( Plugin::class, 'is_valid_key' );
		$this->make_accessible( $is_valid_key );
		$is_valid_key->setValue( $plugin, false );

		$get_subscribers = new \ReflectionMethod( Plugin::class, 'get_subscribers' );
		$this->make_accessible( $get_subscribers );
		$subscribers = $get_subscribers->invoke( $plugin );

		$this->assertContains( 'admin_bar_subscriber', $subscribers );

		$subscriber = apply_filters( 'rocket_container', null )->get( 'admin_bar_subscriber' );
		$events     = $subscriber::get_subscribed_events();

		$this->assertInstanceOf( Subscriber::class, $subscriber );
		$this->assertSame( 'enqueue_admin_bar_style', $events['wp_enqueue_scripts'] );
	}

	public function testShouldEnqueueOnFrontEndForPrivilegedUser() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		add_filter( 'show_admin_bar', '__return_true' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertTrue( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
		$this->assertSame( [ 'admin-bar' ], wp_styles()->registered['wpr-admin-bar']->deps );
	}

	public function testShouldEnqueueInAdminForPrivilegedUser() {
		$this->user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		set_current_screen( 'dashboard' );

		// Call our callback directly: the global hook would run unrelated subscribers' callbacks.
		apply_filters( 'rocket_container', null )->get( 'admin_bar_subscriber' )->enqueue_admin_bar_style();

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
		add_filter( 'show_admin_bar', '__return_false' );

		do_action( 'wp_enqueue_scripts' );

		$this->assertFalse( wp_style_is( 'wpr-admin-bar', 'enqueued' ) );
	}
}
