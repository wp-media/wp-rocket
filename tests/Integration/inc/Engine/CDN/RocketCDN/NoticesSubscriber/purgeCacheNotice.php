<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::purge_cache_notice
 * @uses ::rocket_notice_html
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_PurgeCacheNotice extends TestCase {
	use IsolateHookTrait;
	use HttpRequestTrait;

	/**
	 * HttpRequestTrait fixture config. This class has no fixture file, so it stays an
	 * empty array unless a test populates $this->config['http'].
	 *
	 * @var array
	 */
	protected $config = [];

	public static function set_up_before_class() {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );
	}

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		// ModPagespeed::has_pagespeed() is unrelated to this notice; priming the transient
		// stops its admin_notices callback from hitting the network (home_url()).
		set_transient( 'rocket_mod_pagespeed_enabled', 0 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'current_screen' );

		// Not explicitly deleted here: testShouldDisplayNoticeWhenTransient mocks
		// delete_transient() with a strict once()->with('rocketcdn_purge_cache_response')
		// expectation via Brain\Monkey, active until parent::tear_down() runs. A second,
		// differently-argued call to the (still mocked) function here would break that
		// expectation. WP core's per-test DB transaction rollback clears the option instead.
		$this->tear_down_http();

		parent::tear_down();
	}

	private function get_notice( $status = 'success', $message = '' ) {
		return $this->format_the_html( '<div class="notice notice-' . $status . ' is-dismissible">
		<p>' . $message . '</p>
		</div>' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should not display notice when current user doesn't have capability
	 */
	public function testShouldNotDisplayNoticeWhenNoPermissions() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display notice when not on WP Rocket settings page
	 */
	public function testShouldNotDisplayNoticeWhenNotRocketPage() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display notice when there is no transient value
	 */
	public function testShouldNotDisplayNoticeWhenNoTransient() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should display notice when the transient value is set
	 */
	public function testShouldDisplayNoticeWhenTransient() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_purge_cache_response', [ 'status' => 'success', 'message' => 'RocketCDN cache purge successful.' ], MINUTE_IN_SECONDS );

		Functions\expect('delete_transient')
		->once()
		->with( 'rocketcdn_purge_cache_response' );

		$this->assertStringContainsString( $this->get_notice( 'success', 'RocketCDN cache purge successful.' ), $this->getActualHtml() );
	}
}
