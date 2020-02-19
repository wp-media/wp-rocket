<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use Brain\Monkey\Functions;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @group RocketCDN
 */
class Test_DisplayRocketcdnCta extends FilesystemTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $notices;
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure      = [
		'views' => [
			'settings' => [
				'rocketcdn' => [
					'cta-big.php'   => '',
					'cta-small.php' => '',
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

		$this->assertNull( $this->notices->display_rocketcdn_cta() );
	}

	public function testShouldReturnNullWhenActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
			[
				'subscription_status' => 'running',
			]
		);

		$this->assertNull( $this->notices->display_rocketcdn_cta() );
	}

	public function testShouldDisplayBigCTANoPromoWhenDefault() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );
		Functions\when( 'number_format_i18n' )->returnArg();

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
			[
				'subscription_status' => 'cancelled',
			]
		);

		$this->api_client->method( 'get_pricing_data' )
			->willReturn(
				[
					'monthly_price'            => 7.99,
					'is_discount_active'       => false,
					'discount_campaign_name'   => '',
					'end_date'                 => 0,
					'discounted_price_monthly' => 6.9,
				]
		);

		$this->setOutputCallback(
				function( $output ) {
					return preg_replace( "/\r|\n|\t/", '', $output );
				}
			);
		$this->expectOutputString(
			'<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">RocketCDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$this->notices->display_rocketcdn_cta()
		);
	}

	public function testShouldDisplaySmallCTAWhenBigHidden() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( true );
		Functions\when( 'number_format_i18n' )->returnArg();

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
			[
				'subscription_status' => 'cancelled',
			]
		);

		$this->api_client->method( 'get_pricing_data' )
			->willReturn(
				[
					'monthly_price'            => 7.99,
					'is_discount_active'       => false,
					'discount_campaign_name'   => '',
					'end_date'                 => 0,
					'discounted_price_monthly' => 6.9,
				]
		);

		$this->setOutputCallback(
			function( $output ) {
				return preg_replace( "/\r|\n|\t/", '', $output );
			}
			);
		$this->expectOutputString(
			'<div class="wpr-rocketcdn-cta-small notice-alt notice-warning " id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta wpr-isHidden" id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">RocketCDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$this->notices->display_rocketcdn_cta()
		);
	}

	public function testShouldDisplayBigCTAPromoWhenPromoActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );
		Functions\when( 'number_format_i18n' )->returnArg();

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
			[
				'subscription_status' => 'cancelled',
			]
		);

		$this->api_client->method( 'get_pricing_data' )
			->willReturn(
				[
					'monthly_price'            => 7.99,
					'is_discount_active'       => true,
					'discount_campaign_name'   => 'Launch',
					'end_date'                 => '2020-01-01',
					'discounted_price_monthly' => 6.90,
				]
		);

		$this->setOutputCallback(
				function( $output ) {
					return preg_replace( "/\r|\n|\t/", '', $output );
				}
			);
		$this->expectOutputString(
			'<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><div class="wpr-flex wpr-rocketcdn-promo"><h3 class="wpr-title1">Launch</h3><p class="wpr-title2 wpr-rocketcdn-promo-date">Valid until 2020-01-01 only!</p></div><section class="wpr-rocketcdn-cta-content"><h3 class="wpr-title2">RocketCDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del>$7.99</del></h4><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$6.9*</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a></div><button class="wpr-rocketcdn-cta-close" id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button><p>* $6.9/month for 12 months then $7.99/month. You can cancel your subscription at any time.</p></div>',
			$this->notices->display_rocketcdn_cta()
		);
	}

	public function testShouldDisplayErrorMessageWhenPricingAPINotAvailable() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'is_wp_error' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( false );

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
			[
				'subscription_status' => 'cancelled',
			]
		);

		$wp_error = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive( 'get_error_message' )
			->andReturn( 'RocketCDN is not available at the moment. Please retry later' );

		$this->api_client->method( 'get_pricing_data' )
			->willReturn( $wp_error );

		$this->setOutputCallback(
				function( $output ) {
					return preg_replace( "/\r|\n|\t/", '', $output );
				}
			);
		$this->expectOutputString(
			'<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">RocketCDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><p>RocketCDN is not available at the moment. Please retry later</p></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$this->notices->display_rocketcdn_cta()
		);
	}
}
