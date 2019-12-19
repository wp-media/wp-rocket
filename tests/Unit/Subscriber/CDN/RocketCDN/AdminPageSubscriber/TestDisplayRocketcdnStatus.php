<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber
 * @group RocketCDN
 */
class TestDisplayRocketcdnStatus extends TestCase {
	/**
	 * @covers ::display_rocketcdn_status
	 */
	public function testShouldOutputNoSubscriptionWhenInactive() {
		$this->mockCommonWpFunctions();

		Functions\when('get_transient')->justReturn(
			[
				'is_active' => false,
				'subscription_status' => 'cancelled',
				'subscription_next_date_update' => '2020-01-01'
			]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
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
	 * @covers ::display_rocketcdn_status
	 */
	public function testShouldOutputSubscriptionDataWhenActive() {
		$this->mockCommonWpFunctions();

		Functions\when('get_transient')->justReturn(
			[
				'is_active' => true,
				'subscription_status' => 'active',
				'subscription_next_date_update' => '2020-01-01'
			]
		);

		Functions\when('get_option')->justReturn('Y-m-d');
		Functions\when('date_i18n')->justReturn('2020-01-01');
		Functions\When( 'rocket_direct_filesystem')->alias( function() {
			$wp_fs = $this->getMockBuilder( 'WP_Filesystem_Direct' )
							->setMethods( [
								'is_readable',
							])
							->getMock();
			$wp_fs->method('is_readable')->will($this->returnCallback('is_readable'));
			return $wp_fs;
		});

		$page = new AdminPageSubscriber( 'views/settings/rocketcdn');
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