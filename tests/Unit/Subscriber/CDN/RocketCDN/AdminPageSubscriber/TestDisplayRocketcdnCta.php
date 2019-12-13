<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 * @group RocketCDN
 */
class TestDisplayRocketcdnCta extends TestCase {
	/**
	 * @covers ::display_rocketcdn_cta
	 */
	public function testShouldReturnNullWhenActive() {
		Functions\when('get_transient')->justReturn(
			[
				'is_active' => true,
			]
		);

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
		$this->assertNull($page->display_rocketcdn_cta());
	}

	/**
	 * @covers ::display_rocketcdn_cta
	 */
	public function testShouldDisplayBigCTANoPromoWhenDefault() {
		$this->mockCommonWpFunctions();

		Functions\when('get_transient')->justReturn(
			[
				'is_active' => false,
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
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
		<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">Rocket CDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
					High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong>				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
					Easy configuration: the <strong>best CDN settings</strong> are automatically applied				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
					WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
								<h4 class="wpr-rocketcdn-pricing-current">
				<span class="wpr-title1">$7.99</span> / month				</h4>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<div class="wpr-rocketcdn-cta-footer">
		<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a>
	</div>
	<button class="wpr-rocketcdn-cta-close--no-promo"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button>
</div>',
			$page->display_rocketcdn_cta()
		);
	}

	/**
	 * @covers ::display_rocketcdn_cta
	 */
	public function testShouldDisplaySmallCTAWhenBigHidden() {
		$this->mockCommonWpFunctions();

		Functions\when('get_transient')->justReturn(
			[
				'is_active' => false,
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
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning " id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div><div class="wpr-rocketcdn-cta wpr-isHidden" id="wpr-rocketcdn-cta">
		<section class="wpr-rocketcdn-cta-content--no-promo">
		<h3 class="wpr-title2">Rocket CDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
					High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong>				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
					Easy configuration: the <strong>best CDN settings</strong> are automatically applied				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
					WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
								<h4 class="wpr-rocketcdn-pricing-current">
				<span class="wpr-title1">$7.99</span> / month				</h4>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<div class="wpr-rocketcdn-cta-footer">
		<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a>
	</div>
	<button class="wpr-rocketcdn-cta-close--no-promo"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button>
</div>',
			$page->display_rocketcdn_cta()
		);
	}

	/**
	 * @covers ::display_rocketcdn_cta
	 */
	public function testShouldDisplayBigCTAPromoWhenPromoActive() {
		$this->mockCommonWpFunctions();

		Functions\when('get_transient')->justReturn(
			[
				'is_active' => false,
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
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});
		Functions\when('number_format_i18n')->returnArg();

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
		$this->expectOutputString('<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title">Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network.</strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
		</div>
	</div>
</div><div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
		<div class="wpr-flex wpr-rocketcdn-promo">
		<h3 class="wpr-title1">Launch</h3>
		<p class="wpr-title2 wpr-rocketcdn-promo-date">
			Valid until 2020-01-01 only!		</p>
	</div>
		<section class="wpr-rocketcdn-cta-content">
		<h3 class="wpr-title2">Rocket CDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
					High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong>				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
					Easy configuration: the <strong>best CDN settings</strong> are automatically applied				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
					WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
								<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del>$7.99</del></h4>
								<h4 class="wpr-rocketcdn-pricing-current">
				<span class="wpr-title1">$6.9</span> / month				</h4>
				<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
			</div>
		</div>
	</section>
	<div class="wpr-rocketcdn-cta-footer">
		<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about Rocket CDN</a>
	</div>
	<button class="wpr-rocketcdn-cta-close"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text">Reduce this banner</span></button>
</div>',
			$page->display_rocketcdn_cta()
		);
	}
}