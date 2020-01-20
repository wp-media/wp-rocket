<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_manage_subscription
 * @group  RocketCDN
 */
class Test_DisplayManageSubscription extends TestCase {
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			''
		);
	}

	private function getActualHtml() {
		ob_start();
		$this->page->display_manage_subscription();
		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should return not render the HTML when the subscription is inactive.
	 */
	public function testShouldNotRenderButtonHTMLWhenSubscriptionInactive() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( ['subscription_status' => 'cancelled' ] );
		$this->assertEmpty( $this->getActualHtml() );
	}

	/**
	 * Test should render the manage subscription button HTML when the subscription is active.
	 */
	public function testShouldRenderButtonHTMLWhenSubscriptionActive() {
		$this->mockCommonWpFunctions();

		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn( ['subscription_status' => 'running' ] );

		$expected = <<<HTML
<p class="wpr-rocketcdn-subscription">
	<button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button>
</p>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}
}
