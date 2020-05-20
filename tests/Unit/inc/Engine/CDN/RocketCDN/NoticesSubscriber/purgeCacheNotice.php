<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::purge_cache_notice
 *
 * @group RocketCDN
 */
class Test_PurgeCacheNotice extends TestCase {
	private $api_client;

	public function setUp() {
		parent::setUp();

        $this->api_client = Mockery::mock( 'WP_Rocket\Engine\CDN\RocketCDN\APIClient' );
    }

	/**
	 * Test should return null when current user doesn't have capability
	 */
	public function testShouldReturnNullWhenNoPermissions() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->purge_cache_notice() );
	}

	/**
	 * Test should return null when not on WP Rocket settings page
	 */
	public function testShouldReturnNullWhenNotRocketPage() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'general' ];
		});

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->purge_cache_notice() );
	}

	/**
	 * Test should return null when the transient is not set
	 */
	public function testShouldReturnNullWhenNoTransient() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_transient')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->purge_cache_notice() );
	}

	/**
	 * Test should display notice on successful purge action
	 */
	public function testShouldDisplayNoticeWhenPurgeAction() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_transient')->justReturn([
			'status'  => 'success',
			'message' => 'RocketCDN cache purge successful.',
		]);

		Functions\expect('delete_transient')
		->once()
		->with('rocketcdn_purge_cache_response');

		Functions\expect('rocket_notice_html')
		->once()
		->with([
			'status'  => 'success',
			'message' => 'RocketCDN cache purge successful.',
		]);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$page->purge_cache_notice();
	}
}
