<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the RocketCDN upsell rules shared by the CDN tab banner and the Dashboard sidebar card.
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::can_display_rocketcdn_upsell
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::add_rocketcdn_addon_card
 * @group  RocketCDN
 * @group  RocketCDNNotices
 */
class Test_RocketcdnUpsell extends TestCase {
	private $api_client;
	private $subscription_controller;
	private $user;
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );

		$this->subscriber = new NoticesSubscriber(
			$this->api_client,
			Mockery::mock( Beacon::class ),
			Mockery::mock( Tracking::class ),
			'',
			Mockery::mock( Options_Data::class ),
			$this->subscription_controller,
			$this->user
		);
	}

	private function configure( array $config ) {
		$config = array_merge(
			[
				'filter'      => true,
				'white_label' => false,
				'reseller'    => false,
				'live'        => true,
			],
			$config
		);

		Filters\expectApplied( 'rocket_display_rocketcdn_cta' )->with( true )->andReturn( $config['filter'] );
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $config ) {
				return 'WP_ROCKET_WHITE_LABEL_ACCOUNT' === $name ? $config['white_label'] : $default;
			}
		);
		$this->user->shouldReceive( 'is_reseller_account' )->andReturn( $config['reseller'] );
		Functions\when( 'rocket_is_live_site' )->justReturn( $config['live'] );
	}

	public function testCanDisplayWhenNothingBlocksIt() {
		$this->configure( [] );

		$this->assertTrue( $this->subscriber->can_display_rocketcdn_upsell() );
	}

	public function testCannotDisplayWhenTheFilterSaysNo() {
		$this->configure( [ 'filter' => false ] );

		$this->assertFalse( $this->subscriber->can_display_rocketcdn_upsell() );
	}

	public function testCannotDisplayForWhiteLabel() {
		$this->configure( [ 'white_label' => true ] );

		$this->assertFalse( $this->subscriber->can_display_rocketcdn_upsell() );
	}

	public function testCannotDisplayForReseller() {
		$this->configure( [ 'reseller' => true ] );

		$this->assertFalse( $this->subscriber->can_display_rocketcdn_upsell() );
	}

	public function testCannotDisplayOnNonLiveSite() {
		$this->configure( [ 'live' => false ] );

		$this->assertFalse( $this->subscriber->can_display_rocketcdn_upsell() );
	}

	public function testShouldBeHookedOnTheAddonsFilterAtPriority20() {
		$events = NoticesSubscriber::get_subscribed_events();

		$this->assertSame( [ 'add_rocketcdn_addon_card', 20 ], $events['rocket_dashboard_sidebar_addons'] );
	}

	public function subscriptionCases() {
		return [
			'noSubscription'  => [ false, false, true ],
			'freePlan'        => [ true, false, true ],
			'paidNotRunning'  => [ false, true, true ],
			'paidRunning'     => [ true, true, false ],
		];
	}

	/**
	 * @dataProvider subscriptionCases
	 */
	public function testCardDependsOnTheSubscription( $active, $paid, $shown ) {
		$this->configure( [] );
		$this->api_client->shouldReceive( 'get_subscription_data' )->once();
		$this->subscription_controller->shouldReceive( 'has_active_subscription' )->andReturn( $active );
		$this->subscription_controller->shouldReceive( 'is_paid' )->andReturn( $paid );

		$cards = $this->subscriber->add_rocketcdn_addon_card( [ [ 'id' => 'imagify' ] ] );

		$this->assertCount( $shown ? 2 : 1, $cards );
		$this->assertSame( 'imagify', $cards[0]['id'] );
	}

	public function testCardDataShape() {
		$this->configure( [] );
		$this->api_client->shouldReceive( 'get_subscription_data' )->once();
		$this->subscription_controller->shouldReceive( 'has_active_subscription' )->andReturn( false );
		$this->subscription_controller->shouldReceive( 'is_paid' )->andReturn( false );

		$card = $this->subscriber->add_rocketcdn_addon_card( [] )[0];

		$this->assertSame( 'rocketcdn', $card['id'] );
		$this->assertSame( 'Deliver your content faster', $card['title'] );
		$this->assertSame( 'Propel Your Content at the Speed of Light', $card['lead'] );
		$this->assertSame( 'from 100+ edge locations with unlimited bandwidth.', $card['text'] );
		$this->assertSame(
			[
				'Faster content delivery across your entire website',
				'No bandwidth limits, no hidden costs',
				'100+ edge locations for wider global coverage',
			],
			$card['list']
		);
		$this->assertSame( 'Upgrade Now', $card['button_label'] );
		$this->assertSame( '#page_cdn', $card['button_url'] );
		$this->assertFalse( $card['is_external'] );
	}

	public function hiddenCases() {
		return [
			'reseller even when the filter is forced true' => [ [ 'reseller' => true ] ],
			'white label'                                  => [ [ 'white_label' => true ] ],
			'non live site'                                => [ [ 'live' => false ] ],
			'filter says no'                               => [ [ 'filter' => false ] ],
		];
	}

	/**
	 * @dataProvider hiddenCases
	 */
	public function testCardHiddenWhenTheUpsellCannotBeDisplayed( $config ) {
		$this->configure( $config );
		$this->api_client->shouldReceive( 'get_subscription_data' )->never();

		$this->assertSame( [], $this->subscriber->add_rocketcdn_addon_card( [] ) );
	}
}
