<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @group RocketCDN
 */
class Test_DisplayRocketcdnCta extends TestCase {
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
	 * Test should return null when RocketCDN is active
	 */
	public function testShouldReturnNullWhenActive() {
		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => true,
			]
		);

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->assertNull($page->display_rocketcdn_cta());
	}

	/**
	 * test should display the big CTA without promo
	 */
	public function testShouldDisplayBigCTANoPromoWhenDefault() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => false,
			]
		);

		$this->api_client->method('get_pricing_data')
			->willReturn(
				[
					'monthly_price' => 7.99,
					'is_discount_active' => false,
					'discount_campaign_name' => '',
					'end_date' => 0,
					'discounted_price_monthly' => 6.9,
				]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->setOutputCallback(function($output) {
			return preg_replace("/\r|\n|\t/", '', $output);
		});
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">Rocket CDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$page->display_rocketcdn_cta()
		);
	}

	/**
	 * Test should display the small CTA when the big one is hidden
	 */
	public function testShouldDisplaySmallCTAWhenBigHidden() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => false,
			]
		);

		$this->api_client->method('get_pricing_data')
			->willReturn(
				[
					'monthly_price' => 7.99,
					'is_discount_active' => false,
					'discount_campaign_name' => '',
					'end_date' => 0,
					'discounted_price_monthly' => 6.9,
				]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(true);
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->setOutputCallback(function($output) {
			return preg_replace("/\r|\n|\t/", '', $output);
		});
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning " id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta wpr-isHidden" id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">Rocket CDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$page->display_rocketcdn_cta()
		);
	}

	/**
	 * Test should display the big CTA with the promo when active
	 */
	public function testShouldDisplayBigCTAPromoWhenPromoActive() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => false,
			]
		);

		$this->api_client->method('get_pricing_data')
			->willReturn(
				[
					'monthly_price' => 7.99,
					'is_discount_active' => true,
					'discount_campaign_name' => 'Launch',
					'end_date' => '2020-01-01',
					'discounted_price_monthly' => 6.90,
				]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');

		$this->setOutputCallback(function($output) {
			return preg_replace("/\r|\n|\t/", '', $output);
		});
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><div class="wpr-flex wpr-rocketcdn-promo"><h3 class="wpr-title1">Launch</h3><p class="wpr-title2 wpr-rocketcdn-promo-date">Valid until 2020-01-01 only!</p></div><section class="wpr-rocketcdn-cta-content"><h3 class="wpr-title2">Rocket CDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del>$7.99</del></h4><h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$6.9*</span> / month</h4><button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a></div><button class="wpr-rocketcdn-cta-close"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div><p>* $6.9/month for 12 months then $7.99/month. You can cancel your subscription at any time.</p>',
			$page->display_rocketcdn_cta()
		);
	}

	/**
	 * Test should have an error message instead of pricing when the pricing API is not available
	 */
	public function testShouldDisplayErrorMessageWhenPricingAPINotAvailable() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => false,
			]
		);

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'RocketCDN is not available at the moment. Plese retry later' );

		$this->api_client->method('get_pricing_data')
			->willReturn( $wp_error );

		Functions\when('is_wp_error')->justReturn(true);
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\when('get_user_meta')->justReturn(false);
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});

		$page = new NoticesSubscriber( $this->api_client, 'views/settings/rocketcdn');
		$this->setOutputCallback(function($output) {
			return preg_replace("/\r|\n|\t/", '', $output);
		});
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small"><div class="wpr-flex"><section><h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3></section><div><button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button></div></div></div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta"><section class="wpr-rocketcdn-cta-content--no-promo"><h3 class="wpr-title2">Rocket CDN</h3><p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p><div class="wpr-flex"><ul class="wpr-rocketcdn-features"><li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li><li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li><li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li></ul><div class="wpr-rocketcdn-pricing"><p>RocketCDN is not available at the moment. Plese retry later</p></div></div></section><div class="wpr-rocketcdn-cta-footer"><a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a></div><button class="wpr-rocketcdn-cta-close--no-promo"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button></div>',
			$page->display_rocketcdn_cta()
		);
	}
}