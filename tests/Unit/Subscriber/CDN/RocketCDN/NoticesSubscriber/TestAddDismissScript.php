<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers\WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @group RocketCDN
 */
class TestAddDismissScript extends TestCase {
	private $api_client;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
	}

	/**
	 * @covers ::add_dismiss_script
	 */
	public function testShouldNotAddScriptWhenNoCapability() {
		Functions\when('current_user_can')->justReturn(false);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * @covers ::add_dismiss_script
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
	 * @covers ::add_dismiss_script
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
	 * @covers ::add_dismiss_script
	 */
	public function testShouldNotAddScriptWhenActive() {
		Functions\when('current_user_can')->justReturn(true);
		Functions\when('get_current_screen')->alias(function() {
			return (object) [ 'id' => 'settings_page_wprocket' ];
		});
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);

		$this->api_client->method('get_subscription_data')
			->willReturn(['is_active' => true]);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		
		$this->assertNull($page->add_dismiss_script());
	}

	/**
	 * @covers ::add_dismiss_script
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
			->willReturn(['is_active' => false]);

		Functions\when('wp_create_nonce')->justReturn('123456');
		Functions\when('admin_url')->justReturn('https://example.org/wp-admin/admin-ajax.php');

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->expectOutputString("		<script>
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
		</script>
		");
		$page->add_dismiss_script();
	}
}