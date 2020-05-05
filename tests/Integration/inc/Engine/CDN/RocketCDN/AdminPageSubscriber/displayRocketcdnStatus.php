<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 *
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses ::rocket_is_live_site
 * @uses ::rocket_get_constant
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_direct_filesystem
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_DisplayRocketcdnStatus extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		update_option( 'date_format', 'Y-m-d' );
	}

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
		do_action( 'rocket_dashboard_after_account_data' );

		return $this->format_the_html( ob_get_clean() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		set_transient(
			'rocketcdn_status',
			[
				'is_active'                     => false,
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => '2020-01-01',
			],
			MINUTE_IN_SECONDS
		);

		$callback = function() {
			return 'http://localhost';
		};

		$expected = <<<HTML
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">RocketCDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<span class="wpr-infoAccount wpr-isInvalid">RocketCDN is unavailable on local domains and staging sites.</span>
</div>
HTML;

		add_filter( 'home_url', $callback );

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );

		remove_filter( 'home_url', $callback );
	}

	public function testShouldRenderNoSubscriptionHTMLWhenCancelled() {
		set_transient(
			'rocketcdn_status',
			[
				'is_active'                     => false,
				'subscription_status'           => 'cancelled',
				'subscription_next_date_update' => '2020-01-01',
			],
			MINUTE_IN_SECONDS
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

	public function testShouldRenderHTMLWhenSubscriptionIsRunning() {
		set_transient(
			'rocketcdn_status',
			[
				'is_active'                     => true,
				'subscription_status'           => 'running',
				'subscription_next_date_update' => '2020-01-01',
			],
			MINUTE_IN_SECONDS
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
