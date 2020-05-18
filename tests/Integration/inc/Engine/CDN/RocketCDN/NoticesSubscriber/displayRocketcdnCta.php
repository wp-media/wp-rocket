<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Error;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @uses   ::rocket_is_live_site
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_pricing_data
 * @uses   \WP_Rocket\Abstract_Render::generate
 * @uses   ::rocket_direct_filesystem
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnCta extends TestCase {

	public function setUp() {
		parent::setUp();

		update_option( 'date_format', 'Y-m-d' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_before_cdn_sections' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		$callback = function() {
			return 'http://localhost';
		};

		$not_expected = $this->format_the_html( '<div class="wpr-rocketcdn-cta-small notice-alt notice-warning wpr-isHidden" id="wpr-rocketcdn-cta-small">
			<div class="wpr-flex">
				<section>
					<h3 class="notice-title">Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.</strong></h3>
				</section>
				<div>
					<button class="wpr-button" id="wpr-rocketcdn-open-cta">Learn More</button>
				</div>
			</div>
		</div>
		<div class="wpr-rocketcdn-cta " id="wpr-rocketcdn-cta">
			<section class="wpr-rocketcdn-cta-content--no-promo">
				<h3 class="wpr-title2">RocketCDN</h3>
				<p class="wpr-rocketcdn-cta-subtitle">Speed up your website thanks to:</p>
				<div class="wpr-flex">
					<ul class="wpr-rocketcdn-features">
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">High performance Content Delivery Network (CDN) with <strong>unlimited bandwith</strong></li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">Easy configuration: the <strong>best CDN settings</strong> are automatically applied</li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">WP Rocket integration: the CDN option is <strong>automatically configured</strong> in our plugin</li>
					</ul>
					<div class="wpr-rocketcdn-pricing">
						<h4 class="wpr-rocketcdn-pricing-current"><span class="wpr-title1">$7.99</span> / month</h4>
						<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Get Started</button>
					</div>
				</div>
			</section>
			<div class="wpr-rocketcdn-cta-footer">
				<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer">Learn more about RocketCDN</a>
			</div>
			<button class="wpr-rocketcdn-cta-close--no-promo" id="wpr-rocketcdn-close-cta">
				<span class="screen-reader-text">Reduce this banner</span>
			</button>
		</div>' );

		add_filter( 'home_url', $callback );

		$this->assertNotContains( $not_expected, $this->getActualHtml() );

		remove_filter( 'home_url', $callback );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayPerData( $data, $expected, $config ) {
		set_transient( 'rocketcdn_status', $data['rocketcdn_status'], MINUTE_IN_SECONDS );

		if ( isset( $expected['integration']['not_expected'] ) ) {
			foreach ( $expected['integration']['not_expected'] as $not_expected ) {
				$this->assertNotContains( $not_expected, $this->getActualHtml() );
			}

			return;
		}

		set_transient(
			'rocketcdn_pricing',
			$config['is_wp_error']
				? new WP_Error( 'rocketcdn_error', $data['rocketcdn_pricing'] )
				: $data['rocketcdn_pricing'],
			MINUTE_IN_SECONDS
		);

		if ( $config['rocket_rocketcdn_cta_hidden'] ) {
			$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
			wp_set_current_user( $user_id );
			add_user_meta( $user_id, 'rocket_rocketcdn_cta_hidden', true );
		}

		$this->assertContains( $this->format_the_html( $expected['integration'] ), $this->getActualHtml() );
	}
}
