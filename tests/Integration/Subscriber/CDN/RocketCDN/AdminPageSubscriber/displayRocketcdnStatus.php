<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
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

	/**
	 * Test should render the "no subscription" HTML when the subscription status is "cancelled."
	 */
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

	/**
	 * Test should render HTML when the subscription status is "running".
	 */
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
