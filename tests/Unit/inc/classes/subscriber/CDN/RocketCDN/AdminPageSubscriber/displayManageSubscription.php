<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\CDN\RocketCDN;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_manage_subscription
 * @group  RocketCDN
 */
class Test_DisplayManageSubscription extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( APIClient::class );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( Options_Data::class ),
			$this->createMock( Beacon::class ),
			'views/settings/rocketcdn'
		);
	}

	private function getActualHtml() {
		ob_start();
		$this->page->display_manage_subscription();
		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->page->display_manage_subscription() );
	}

	public function testShouldNotRenderButtonHTMLWhenSubscriptionInactive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		$this->api_client->expects( $this->once() )
			->method( 'get_subscription_data' )
			->willReturn( [ 'subscription_status' => 'cancelled' ] );

		$this->assertEmpty( $this->getActualHtml() );
	}

	public function testShouldRenderButtonHTMLWhenSubscriptionActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		$this->api_client->expects( $this->once() )
			->method( 'get_subscription_data' )
			->willReturn( [ 'subscription_status' => 'running' ] );

		$expected = <<<HTML
<p class="wpr-rocketcdn-subscription">
	<button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button>
</p>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}
}
