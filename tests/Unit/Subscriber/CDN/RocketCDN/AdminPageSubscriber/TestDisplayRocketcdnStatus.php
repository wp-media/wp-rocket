<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 * @group RocketCDN
 */
class TestDisplayRocketcdnStatus extends TestCase {
	private $api_client;
	private $options;
	private $beacon;
	private $filesystem;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock('WP_Rocket\CDN\RocketCDN\APIClient');
		$this->options    = $this->createMock('WP_Rocket\Admin\Options_Data');
		$this->beacon     = $this->createMock('WP_Rocket\Admin\Settings\Beacon');
		$this->filesystem = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
		$this->filesystem->method('is_readable')->will($this->returnCallback('is_readable'));
	}

	/**
	 * Test should output HTML for an inactive subscription
	 */
	public function testShouldOutputNoSubscriptionWhenInactive() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn([
				'is_active' => false,
				'subscription_status' => 'cancelled',
				'subscription_next_date_update' => '2020-01-01'
			]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn');
		$this->expectOutputString('<div class="wpr-optionHeader">
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
			$page->display_rocketcdn_status()
		);
	}

	/**
	 * Test should output HTML for an active subscription
	 */
	public function testShouldOutputSubscriptionDataWhenActive() {
		$this->mockCommonWpFunctions();

		$this->api_client->method('get_subscription_data')
			->willReturn(
			[
				'is_active' => true,
				'subscription_status' => 'active',
				'subscription_next_date_update' => '2020-01-01'
			]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			return $this->filesystem;
		});

		$page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn');
		$this->expectOutputString('<div class="wpr-optionHeader">
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
			$page->display_rocketcdn_status()
		);
	}
}