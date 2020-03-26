<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Settings\Beacon;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnStatus extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->page       = Mockery::mock(
			AdminPageSubscriber::class . '[generate]',
			[
				$this->api_client,
				Mockery::mock( Options_Data::class ),
				Mockery::mock( Beacon::class ),
				'views/settings/rocketcdn',
			]
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayPerData( $subscription_data, $expected, $config ) {
		Functions\when( 'rocket_is_live_site' )->justReturn( ( 'http://example.org' === $config['home_url'] ) );

		if ( ! $config['get_option'] ) {
			Functions\expect( 'get_option' )->never();
		} else {
			Functions\when( 'get_option' )->justReturn( $config['get_option'] );
		}

		if ( ! $config['date_i18n'] ) {
			Functions\expect( 'date_i18n' )->never();
		} else {
			Functions\when( 'date_i18n' )->justReturn( $config['date_i18n'] );
		}

		$this->api_client->shouldReceive( 'get_subscription_data' )
		                 ->once()
		                 ->andReturn( $subscription_data );

		$this->page->shouldReceive( 'generate' )
		           ->once()
		           ->with( 'dashboard-status', $expected['unit'] )
		           ->andReturn( '' );

		$this->expectOutputString( '' );
		$this->page->display_rocketcdn_status();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'displayRocketcdnStatus' );
	}

//	public function testShouldDisplayNothingWhenNotLiveSite() {
//		Functions\when( 'rocket_is_live_site' )->justReturn( false );
//
//		$this->api_client->shouldReceive( 'get_subscription_data' )
//		                 ->once()
//		                 ->andReturn(
//			                 [
//				                 'is_active'                     => false,
//				                 'subscription_status'           => 'cancelled',
//				                 'subscription_next_date_update' => '2020-01-01',
//			                 ]
//		                 );
//
//		$this->page->shouldReceive( 'generate' )
//		           ->once()
//		           ->with(
//			           'dashboard-status',
//			           [
//				           'is_live_site'    => false,
//				           'container_class' => ' wpr-flex--egal',
//				           'label'           => '',
//				           'status_class'    => ' wpr-isInvalid',
//				           'status_text'     => 'No Subscription',
//				           'is_active'       => false,
//			           ]
//		           )
//		           ->andReturn( '' );
//
//		$this->expectOutputString( '' );
//		$this->page->display_rocketcdn_status();
//	}
//
//	public function testShouldOutputNoSubscriptionWhenInactive() {
//		Functions\when( 'rocket_is_live_site' )->justReturn( true );
//		Functions\expect( 'get_option' )->never();
//		Functions\expect( 'date_i18n' )->never();
//
//		$this->api_client->shouldReceive( 'get_subscription_data' )
//		                 ->once()
//		                 ->willReturn(
//			                 [
//				                 'is_active'                     => false,
//				                 'subscription_status'           => 'cancelled',
//				                 'subscription_next_date_update' => '2020-01-01',
//			                 ]
//		                 );
//
//		$expected = <<<HTML
//<div class="wpr-optionHeader">
//	<h3 class="wpr-title2">RocketCDN</h3>
//</div>
//<div class="wpr-field wpr-field-account">
//	<div class="wpr-flex wpr-flex--egal">
//		<div>
//			<span class="wpr-title3"></span>
//			<span class="wpr-infoAccount wpr-isInvalid">No Subscription</span>
//		</div>
//		<div>
//			<a href="#page_cdn" class="wpr-button">Get RocketCDN</a>
//		</div>
//	</div>
//</div>
//HTML;
//
//		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
//	}
//
//	public function testShouldOutputSubscriptionDataWhenActive() {
//		Functions\when( 'rocket_is_live_site' )->justReturn( true );
//		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
//		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );
//
//		$this->api_client->method( 'get_subscription_data' )
//		                 ->willReturn(
//			                 [
//				                 'is_active'                     => true,
//				                 'subscription_status'           => 'running',
//				                 'subscription_next_date_update' => '2020-01-01',
//			                 ]
//		                 );
//
//		$expected = <<<HTML
//<div class="wpr-optionHeader">
//	<h3 class="wpr-title2">RocketCDN</h3>
//</div>
//<div class="wpr-field wpr-field-account">
//	<div class="wpr-flex">
//		<div>
//			<span class="wpr-title3">Next Billing Date</span>
//			<span class="wpr-infoAccount wpr-isValid">2020-01-01</span>
//		</div>
//	</div>
//</div>
//HTML;
//
//		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
//	}
}
