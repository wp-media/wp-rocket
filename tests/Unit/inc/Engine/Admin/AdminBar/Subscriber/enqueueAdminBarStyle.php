<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\AdminBar\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\AdminBar\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\AdminBar\Subscriber
 *
 * @group AdminBar
 */
class Test_EnqueueAdminBarStyle extends TestCase {
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->constants['WP_ROCKET_ASSETS_CSS_URL'] = 'http://example.org/assets/css/';
		$this->rocket_version                         = '3.20';

		$this->subscriber = new Subscriber();
	}

	public function testShouldSubscribeToAdminAndFrontEndEnqueueEvents() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame( 'enqueue_admin_bar_style', $events['admin_enqueue_scripts'] );
		$this->assertSame( 'enqueue_admin_bar_style', $events['wp_enqueue_scripts'] );
		$this->assertTrue( is_callable( [ $this->subscriber, 'enqueue_admin_bar_style' ] ) );
	}

	public function testShouldNotEnqueueWhenAdminBarIsNotShowing() {
		Functions\when( 'is_admin_bar_showing' )->justReturn( false );
		Functions\expect( 'wp_enqueue_style' )->never();

		$this->subscriber->enqueue_admin_bar_style();
	}

	public function testShouldNotEnqueueWhenUserHasNoCapability() {
		Functions\when( 'is_admin_bar_showing' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_enqueue_style' )->never();

		$this->subscriber->enqueue_admin_bar_style();
	}

	public function testShouldEnqueueWhenUserHasOneCapability() {
		Functions\when( 'is_admin_bar_showing' )->justReturn( true );
		Functions\when( 'current_user_can' )->alias(
			function ( $cap ) {
				return 'rocket_purge_cache' === $cap;
			}
		);
		Functions\expect( 'wp_enqueue_style' )
			->once()
			->with( 'wpr-admin-bar', 'http://example.org/assets/css/wpr-admin-bar.css', [ 'admin-bar' ], '3.20' );

		$this->subscriber->enqueue_admin_bar_style();
	}
}
