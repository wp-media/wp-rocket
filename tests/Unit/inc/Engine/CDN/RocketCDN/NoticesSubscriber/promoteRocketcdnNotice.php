<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Tests\StubTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::promote_rocketcdn_notice
 * @group  RocketCDN
 */
class Test_PromoteRocketcdnNotice extends TestCase {
	use StubTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $notices;

	public function setUp() {
		parent::setUp();

		$this->stubRocketGetConstant();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->notices    = Mockery::mock(
			NoticesSubscriber::class . '[generate]',
			[
				$this->api_client,
				'views/settings/rocketcdn',
			]
		);
	}

	public function tearDown() {
		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldDisplayNothingWhenWhiteLabel() {
		$this->white_label = true;
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\expect( 'rocket_is_live_site' )->once()->andReturn( false );
		Functions\expect( 'current_user_can' )->with( 'rocket_manage_options' )->never();
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenNoCapability() {
		Functions\expect( 'rocket_is_live_site' )->once()->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( false );
		Functions\expect( 'get_current_screen' )->never();
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenNotRocketPage() {
		Functions\expect( 'rocket_is_live_site' )->once()->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( true );
		Functions\expect( 'get_current_screen' )->once()->andReturnUsing(
			function () {
				return (object) [ 'id' => 'general' ];
			}
		);
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenDismissed() {
		$this->setUpExpects( true );
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShouldReturnNullWhenActive() {
		$this->setUpExpects( false );

		$this->api_client->shouldReceive( 'get_subscription_data' )
		                 ->once()
		                 ->andReturn( [ 'subscription_status' => 'running' ] );
		$this->notices->shouldReceive( 'generate' )->never();

		$this->assertNull( $this->notices->promote_rocketcdn_notice() );
	}

	public function testShoulDisplayNoticeWhenNotActive() {
		$this->setUpExpects( false );

		$this->api_client->shouldReceive( 'get_subscription_data' )
		                 ->once()
		                 ->andReturn( [ 'subscription_status' => 'cancelled' ] );

		$this->expectOutputString( '' );
		$this->notices->shouldReceive( 'generate' )->once()->with( 'promote-notice' )->andReturnNull();

		$this->notices->promote_rocketcdn_notice();
	}

	private function setUpExpects( $rocket_dismiss_notice ) {
		Functions\expect( 'rocket_is_live_site' )->once()->andReturn( true );
		Functions\expect( 'current_user_can' )->once()->with( 'rocket_manage_options' )->andReturn( true );
		$screen = (object) [ 'id' => 'settings_page_wprocket' ];
		Functions\expect( 'get_current_screen' )->once()->andReturn( $screen );
		Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( 1, 'rocketcdn_dismiss_notice', true )
			->andReturn( $rocket_dismiss_notice );
	}
}
