<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::purge_cache_notice
 * @group RocketCDN
 */
class Test_PurgeCacheNotice extends TestCase {
	private $api_client;

	public function setUp() {
		parent::setUp();

        $this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
    }

	/**
	 * @covers ::purge_cache_notice
	 */
	public function testShouldReturnNullWhenNoPermissions() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->purge_cache_notice() );
	}

	/**
	 * @covers ::purge_cache_notice
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
	 * @covers ::purge_cache_notice
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
	 * @covers ::purge_cache_notice
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