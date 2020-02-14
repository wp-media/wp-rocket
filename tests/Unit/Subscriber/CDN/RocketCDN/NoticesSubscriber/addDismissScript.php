<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers\WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @group RocketCDN
 */
class Test_AddDismissScript extends TestCase {
	private $api_client;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
	}

	/**
	 * Test should not add script when user doesn't have the capability to use it
	 */
	public function testShouldNotAddScriptWhenNoCapability() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * Test should not add script when not on WP Rocket settings page
	 */
	public function testShouldNotAddScriptWhenNotRocketPage() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'general' ];
		});

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * Test should not add script when the notice has been dismissed
	 */
	public function testShouldNotAddScriptWhenDismissed() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(true);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * Test should not add script when RocketCDN is active
	 */
	public function testShouldNotAddScriptWhenActive() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);

		$this->api_client->method('get_subscription_data')
			->willReturn(['subscription_status' => 'running']);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * Test should add script when RocketCDN is inactive
	 */
	public function testShouldAddScriptWhenNotActive() {
		$this->mockCommonWpFunctions();

		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);

		$this->api_client->method('get_subscription_data')
			->willReturn(['subscription_status' => 'cancelled']);

		Functions\when('wp_create_nonce')->justReturn('123456');
		Functions\when('admin_url')->justReturn('https://example.org/wp-admin/admin-ajax.php');

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->setOutputCallback(function($output) {
			return trim($output);
		});
		$this->expectOutputString("<script>
		window.addEventListener( 'load', function() {
			var dismissBtn  = document.querySelectorAll( '#rocketcdn-promote-notice .notice-dismiss, #rocketcdn-promote-notice #rocketcdn-learn-more-dismiss' );

			dismissBtn.forEach(function(element) {
				element.addEventListener( 'click', function( event ) {
					var httpRequest = new XMLHttpRequest(),
						postData    = '';

					postData += 'action=rocketcdn_dismiss_notice';
					postData += '&nonce=123456';
					httpRequest.open( 'POST', 'https://example.org/wp-admin/admin-ajax.php' );
					httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
					httpRequest.send( postData );
				});
			});
		});
		</script>");
		$page->add_dismiss_script();
	}
}
