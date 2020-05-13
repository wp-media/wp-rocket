<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use Mockery;

/**
 * @covers\WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::add_dismiss_script
 * @group RocketCDN
 */
class Test_AddDismissScript extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $notices;

	public function setUp() {
		parent::setUp();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->notices    = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn' );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->notices->add_dismiss_script() );
	}

	public function testShouldNotAddScriptWhenNoCapability() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->notices->add_dismiss_script() );
	}

	public function testShouldNotAddScriptWhenNotRocketPage() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'general' ];
			}
		);

		$this->assertNull( $this->notices->add_dismiss_script() );
	}

	public function testShouldNotAddScriptWhenDismissed() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( true );

		$this->assertNull( $this->notices->add_dismiss_script() );
	}

	public function testShouldNotAddScriptWhenActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );

		$this->api_client->shouldReceive( 'get_subscription_data' )
			->andReturn( [ 'subscription_status' => 'running' ] );

		$this->assertNull( $this->notices->add_dismiss_script() );
	}

	public function testShouldAddScriptWhenNotActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );
		Functions\when( 'wp_create_nonce' )->justReturn( '123456' );
		Functions\when( 'admin_url' )->justReturn( 'https://example.org/wp-admin/admin-ajax.php' );

		$this->api_client->shouldReceive( 'get_subscription_data' )
			->andReturn( [ 'subscription_status' => 'cancelled' ] );

		$this->setOutputCallback(
			function( $output ) {
				return trim( $output );
			}
		);
		$this->expectOutputString(
			"<script>
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
		</script>"
			);
		$this->notices->add_dismiss_script();
	}
}
