<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnStatus extends TestCase {
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
		);

		Functions\expect( 'rocket_direct_filesystem' )
			->once()
			->andReturnUsing( function() {
				return new WP_Filesystem_Direct;
			});

		$this->mockCommonWpFunctions();
	}

	private function getActualHtml() {
		ob_start();
		$this->page->display_rocketcdn_status();
		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should render the "no subscription" HTML when the subscription status is "cancelled."
	 */
	public function testShouldRenderNoSubscriptionHTMLWhenCancelled() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => false,
				                 'subscription_status'           => 'cancelled',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );
		Functions\expect( 'get_option' )->never();
		Functions\expect( 'date_il18n' )->never();

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex wpr-flex--egal">
		<div>
			<span class="wpr-title3"></span>
			<span class="wpr-infoAccount wpr-isInvalid">No Subscription</span>
		</div>
		<div>
			<a href="#page_cdn" class="wpr-button">Get Rocket CDN</a>
		</div>
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	/**
	 * Test should render HTML when the subscription status is "active" but "is_active" is false.
	 */
	public function testShouldRenderHTMLWhenIsActiveFalseAndStatusIsActive() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => false,
				                 'subscription_status'           => 'active',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );
		Functions\expect( 'get_option' )
			->once()
			->with( 'date_format' )
			->andReturn( 'Y-m-d' );
		Functions\expect( 'date_i18n' )
			->once()
			->with( 'Y-m-d', strtotime( '2020-01-01' ) )
			->andReturn( '2020-01-01' );

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex wpr-flex--egal">
		<div>
			<span class="wpr-title3"></span>
			<span class="wpr-infoAccount wpr-isInvalid">2020-01-01</span>
		</div>
		<div>
			<a href="#page_cdn" class="wpr-button">Get Rocket CDN</a>
		</div>		
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	/**
	 * Test should render HTML when the subscription status is "cancelled" but "is_active" is true.
	 */
	public function testShouldRenderHTMLWhenStatusCancelledAndIsActiveTrue() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => true,
				                 'subscription_status'           => 'cancelled',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );
		Functions\expect( 'get_option' )->never();
		Functions\expect( 'date_i18n' )->never();

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex">
		<div>
			<span class="wpr-title3">Next Billing Date</span>
			<span class="wpr-infoAccount wpr-isValid">No Subscription</span>
		</div>	
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	/**
	 * Test should render HTML when the subscription status is "active" and "is_active" is true.
	 */
	public function testShouldRenderHTMLWhenActiveSubscriptionIsActive() {
		$this->api_client->expects( $this->once() )
		                 ->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => true,
				                 'subscription_status'           => 'active',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );
		Functions\expect( 'get_option' )
			->once()
			->with( 'date_format' )
			->andReturn( 'Y-m-d' );
		Functions\expect( 'date_i18n' )
			->once()
			->with( 'Y-m-d', strtotime( '2020-01-01' ) )
			->andReturn( '2020-01-01' );

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex">
		<div>
			<span class="wpr-title3">Next Billing Date</span>
			<span class="wpr-infoAccount wpr-isValid">2020-01-01</span>
		</div>
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}
}
