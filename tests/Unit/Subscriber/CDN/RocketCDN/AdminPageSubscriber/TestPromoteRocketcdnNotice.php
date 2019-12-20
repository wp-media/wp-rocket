<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 * @group RocketCDN
 */
class TestPromoteRocketcdnNotice extends TestCase {
	private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$this->beacon  = $this->createMock('WP_Rocket\Admin\Settings\Beacon');
	}

	/**
	 * @covers ::promote_rocketcdn_notice
	 */
	public function testShouldNotDisplayNoticeWhenNoCapability() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
		
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

		$page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
		
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

		$page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
		
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
		Functions\when('get_transient')->justReturn(['is_active' => true]);

		$page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');
		
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
		Functions\when('get_transient')->justReturn(['is_active' => false]);

		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});

		$page = new AdminPageSubscriber( $this->options, $this->beacon, 'views/settings/rocketcdn');

		$this->expectOutputString('<div class="notice notice-alt notice-warning is-dismissible" id="rocketcdn-promote-notice">
	<h2 class="notice-title">New!</h2>
	<p>Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network!</p>
	<p><a href="#page_cdn" class="wpr-button" id="rocketcdn-learn-more-dismiss">Learn More</a></p>
</div>');
		$page->promote_rocketcdn_notice();
	}
}