<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::promote_rocketcdn_notice
 * @group RocketCDN
 */
class TestPromoteRocketcdnNotice extends TestCase {
	private $api_client;
	private $filesystem;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->filesystem = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
		$this->filesystem->method('is_readable')->will($this->returnCallback('is_readable'));
    }

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShouldNotDisplayNoticeWhenNoCapability() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->promote_rocketcdn_notice());
	}

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShouldNotDisplayNoticeWhenNotRocketPage() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'general' ];
		});

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->promote_rocketcdn_notice());
	}

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShouldNotDisplayNoticeWhenDismissed() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(true);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->promote_rocketcdn_notice());
	}

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShouldNotDisplayNoticeWhenActive() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);

		$this->api_client->method('get_subscription_data')
			->willReturn(['is_active' => true]);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->promote_rocketcdn_notice());
	}

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShoulDisplayNoticeWhenNotActive() {
		$this->mockCommonWpFunctions();

		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);

		$this->api_client->method('get_subscription_data')
			->willReturn(['is_active' => false]);

		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->expectOutputString('<div class="notice notice-alt notice-warning is-dismissible" id="rocketcdn-promote-notice">
	<h2 class="notice-title">New!</h2>
	<p>Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network!</p>
	<p><a href="#page_cdn" class="wpr-button" id="rocketcdn-learn-more-dismiss">Learn More</a></p>
</div>');
		$page->promote_rocketcdn_notice();
	}
}