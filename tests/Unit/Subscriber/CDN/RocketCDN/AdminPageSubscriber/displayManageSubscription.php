<?php
namespace WP_Rocket\Tests\Unit\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\AdminPageSubscriber::display_manage_subscription
 * @group RocketCDN
 */
class Test_DisplayManageSubscription extends TestCase {
    private $api_client;
	private $options;
	private $beacon;

	public function setUp() {
		parent::setUp();

		$this->api_client = $this->createMock('WP_Rocket\CDN\RocketCDN\APIClient');
		$this->options    = $this->createMock('WP_Rocket\Admin\Options_Data');
		$this->beacon     = $this->createMock('WP_Rocket\Admin\Settings\Beacon');
    }

    /**
     * test should return null when the subscription is inactive
     */
    public function testShouldReturnNullWhenSubscriptionInactive() {
        $this->api_client->method('get_subscription_data')
			->willReturn([
				'is_active' => false,
				'subscription_status' => 'cancelled',
				'subscription_next_date_update' => '2020-01-01'
			]
        );

        $page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn');

        $this->assertNull( $page->display_manage_subscription() );
    }

    /**
     * Test should display manage subscription button when subscription is active
     */
    public function testShouldDisplayButtonWhenSubscriptionActive() {
        $this->mockCommonWpFunctions();

        $this->api_client->method('get_subscription_data')
			->willReturn([
				'is_active' => true,
				'subscription_status' => 'active',
				'subscription_next_date_update' => '2020-01-01'
			]
        );

        $page = new AdminPageSubscriber( $this->api_client, $this->options, $this->beacon, 'views/settings/rocketcdn');

        $this->setOutputCallback(function($output) {
			return preg_replace("/\r|\n|\t/", '', $output);
		});
        $this->expectOutputString(
            '<p class="wpr-rocketcdn-subscription"><button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button></p>',
            $page->display_manage_subscription()
        );
    }
}
