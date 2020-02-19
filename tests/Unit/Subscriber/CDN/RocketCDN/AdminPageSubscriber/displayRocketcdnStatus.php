<?php

namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Settings\Beacon;
use WP_Rocket\CDN\RocketCDN\APIClient;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnStatus extends FilesystemTestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;
	protected $rootVirtualDir = 'wp-rocket';
	protected $structure      = [
		'views' => [
			'settings' => [
				'rocketcdn' => [
					'dashboard-status.php' => '',
				],
			],
		],
	];

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock( APIClient::class );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			$this->createMock( Options_Data::class ),
			$this->createMock( Beacon::class ),
			'views/settings/rocketcdn'
		);

		Functions\when( 'rocket_direct_filesystem' )->justReturn( $this->filesystem );
	}

	private function getActualHtml() {
		ob_start();
		$this->page->display_rocketcdn_status();
		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->page->display_rocketcdn_status() );
	}

	public function testShouldOutputNoSubscriptionWhenInactive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\expect( 'get_option' )->never();
		Functions\expect( 'date_i18n' )->never();

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
				[
					'is_active'           => false,
					'subscription_status' => 'cancelled',
					'subscription_next_date_update' => '2020-01-01',
				]
			);

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">RocketCDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex wpr-flex--egal">
		<div>
			<span class="wpr-title3"></span>
			<span class="wpr-infoAccount wpr-isInvalid">No Subscription</span>
		</div>
		<div>
			<a href="#page_cdn" class="wpr-button">Get RocketCDN</a>
		</div>
	</div>
</div>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	public function testShouldOutputSubscriptionDataWhenActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( 'Y-m-d' );
		Functions\when( 'date_i18n' )->justReturn( '2020-01-01' );

		$this->api_client->method( 'get_subscription_data' )
			->willReturn(
					[
						'is_active'           => true,
						'subscription_status' => 'running',
						'subscription_next_date_update' => '2020-01-01',
					]
				);

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">RocketCDN</h3>
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
