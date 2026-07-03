<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\Cloudflare\Auth\AuthFactoryInterface;

/**
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::get_subscribed_events
 *
 * @group Cloudflare
 */
class TestGetSubscribedEvents extends TestCase {

	/**
	 * Test that after_rocket_clean_home is subscribed to auto_purge.
	 */
	public function testShouldContainAfterRocketCleanHome(): void {
		$events = Subscriber::get_subscribed_events();

		$this->assertArrayHasKey( 'after_rocket_clean_home', $events );
		$this->assertSame( 'auto_purge', $events['after_rocket_clean_home'] );
	}

	/**
	 * Test that after_rocket_clean_files is subscribed to auto_purge.
	 */
	public function testShouldContainAfterRocketCleanFiles(): void {
		$events = Subscriber::get_subscribed_events();

		$this->assertArrayHasKey( 'after_rocket_clean_files', $events );
		$this->assertSame( 'auto_purge', $events['after_rocket_clean_files'] );
	}

	/**
	 * Test that the existing rocket_after_clean_domain hook is not affected.
	 */
	public function testShouldPreserveExistingCleanDomainHook(): void {
		$events = Subscriber::get_subscribed_events();

		$this->assertArrayHasKey( 'rocket_after_clean_domain', $events );
		$this->assertSame( 'auto_purge', $events['rocket_after_clean_domain'] );
	}
}
