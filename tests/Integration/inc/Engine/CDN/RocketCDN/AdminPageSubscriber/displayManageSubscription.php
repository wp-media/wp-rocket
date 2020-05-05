<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_manage_subscription
 *
 * @uses ::rocket_is_live_site
 * @uses ::rocket_get_constant
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_DisplayManageSubscription extends TestCase {

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'rocket_after_cdn_sections' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		$callback = function() {
			return 'http://localhost';
		};

		add_filter( 'home_url', $callback );

		$this->assertEmpty( $this->getActualHtml() );

		remove_filter( 'home_url', $callback );
	}

	/**
	 * Test should return not render the HTML when the subscription is inactive.
	 */
	public function testShouldNotRenderButtonHTMLWhenSubscriptionInactive() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertEmpty( $this->getActualHtml() );
	}

	/**
	 * Test should render the manage subscription button HTML when the subscription is active.
	 */
	public function testShouldRenderButtonHTMLWhenSubscriptionActive() {
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ], MINUTE_IN_SECONDS );

		$expected = <<<HTML
<p class="wpr-rocketcdn-subscription">
	<button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button>
</p>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}
}
