<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

/**
 * @covers AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnStatus extends FilesystemTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure = [
			'views' => [
				'settings' => [
					'rocketcdn' => [
						'dashboard-status.php' => ''
					]
				]
			]
	];

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( 'WP_Rocket\CDN\RocketCDN\APIClient' );

		$this->page = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( 'WP_Rocket\Admin\Options_Data' ),
			$this->createMock( 'WP_Rocket\Admin\Settings\Beacon' ),
			'views/settings/rocketcdn'
		);
		Functions\When( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	/**
	 * Test should output HTML for an inactive subscription
	 */
	public function testShouldOutputNoSubscriptionWhenInactive() {
		$this->api_client->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => false,
				                 'subscription_status'           => 'cancelled',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );

		Functions\expect( 'get_option' )->never();
		Functions\expect( 'date_i18n' )->never();

		$this->expectOutputString(
			'<div class="wpr-optionHeader">
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
</div>',
			$this->page->display_rocketcdn_status()
		);
	}

	public function testShouldOutputSubscriptionDataWhenActive() {
		$this->api_client->method( 'get_subscription_data' )
		                 ->willReturn(
			                 [
				                 'is_active'                     => true,
				                 'subscription_status'           => 'active',
				                 'subscription_next_date_update' => '2020-01-01',
			                 ]
		                 );

		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );

		$this->expectOutputString(
			'<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex ">
		<div>
			<span class="wpr-title3">Next Billing Date</span>
			<span class="wpr-infoAccount wpr-isValid">2020-01-01</span>
		</div>
			</div>
</div>',
			$this->page->display_rocketcdn_status()
		);
	}
}
