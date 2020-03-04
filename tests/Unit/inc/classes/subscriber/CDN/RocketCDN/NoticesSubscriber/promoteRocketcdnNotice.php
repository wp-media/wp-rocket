<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\CDN\RocketCDN;

use Brain\Monkey\Functions;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::promote_rocketcdn_notice
 * @group RocketCDN
 */
class Test_PromoteRocketcdnNotice extends FilesystemTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $notices;
	protected $structure = [
		'views' => [
			'settings' => [
				'rocketcdn' => [
					'promote-notice.php' => '',
				],
			],
		],
	];

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( APIClient::class );
		$this->notices    = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn' );

		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenNoCapability() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenNotRocketPage() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'general' ];
			}
		);

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturNullWhenDismissed() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( true );

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );

		$this->api_client->method( 'get_subscription_data' )
			->willReturn( [ 'subscription_status' => 'running' ] );

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShoulDisplayNoticeWhenNotActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_screen' )->alias(
			function() {
				return (object) [ 'id' => 'settings_page_wprocket' ];
			}
		);
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );

		$this->api_client->method( 'get_subscription_data' )
			->willReturn( [ 'subscription_status' => 'cancelled' ] );

		$this->expectOutputString(
			'<div class="notice notice-alt notice-warning is-dismissible" id="rocketcdn-promote-notice">
	<h2 class="notice-title">New!</h2>
	<p>Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network!</p>
	<p><a href="#page_cdn" class="wpr-button" id="rocketcdn-learn-more-dismiss">Learn More</a></p>
</div>'
			);
		$this->notices->promote_rocketcdn_notice();
	}
}
