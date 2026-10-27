<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Subscriber;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Engine\License\Revoked;
use WP_Rocket\Engine\License\Subscriber;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the dashboard sidebar callbacks of \WP_Rocket\Engine\License\Subscriber
 *
 * @group License
 */
class Test_AddDashboardSidebarBanners extends TestCase {
	private $upgrade;
	private $renewal;
	private $revoked;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->upgrade    = Mockery::mock( Upgrade::class );
		$this->renewal    = Mockery::mock( Renewal::class );
		$this->revoked    = Mockery::mock( Revoked::class );
		$this->subscriber = new Subscriber(
			$this->upgrade,
			$this->renewal,
			$this->revoked,
			Mockery::mock( User::class ),
			Mockery::mock( UserClient::class )
		);
	}

	public function testShouldBeHookedOnTheSidebarFiltersAndNotOnTheOldAction() {
		$events = Subscriber::get_subscribed_events();

		$this->assertArrayNotHasKey( 'rocket_before_dashboard_content', $events );
		$this->assertSame(
			[
				[ 'add_renewal_soon_banner', 11 ],
				[ 'add_renewal_expired_banner', 12 ],
				[ 'add_revoked_banner', 13 ],
			],
			$events['rocket_dashboard_sidebar_license']
		);
		$this->assertSame( [ 'add_promo_banner' ], $events['rocket_dashboard_sidebar_promos'] );
	}

	public function licenseCallbacks() {
		return [
			'renewalSoon'    => [ 'add_renewal_soon_banner', 'renewal', 'get_renewal_soon_banner' ],
			'renewalExpired' => [ 'add_renewal_expired_banner', 'renewal', 'get_renewal_expired_banner' ],
			'revoked'        => [ 'add_revoked_banner', 'revoked', 'get_revoked_banner' ],
			'promo'          => [ 'add_promo_banner', 'upgrade', 'get_promo_banner' ],
		];
	}

	/**
	 * @dataProvider licenseCallbacks
	 */
	public function testShouldReturnTheBannerWhenNothingIsDisplayedYet( $callback, $dependency, $method ) {
		$this->$dependency->shouldReceive( $method )->once()->andReturn( '<div>banner</div>' );

		$this->assertSame( '<div>banner</div>', $this->subscriber->$callback( '' ) );
	}

	/**
	 * @dataProvider licenseCallbacks
	 */
	public function testShouldReturnEmptyStringWhenThereIsNoBanner( $callback, $dependency, $method ) {
		$this->$dependency->shouldReceive( $method )->once()->andReturn( '' );

		$this->assertSame( '', $this->subscriber->$callback( '' ) );
	}

	/**
	 * First non-empty wins: a banner already there is returned unchanged, without computing a new one.
	 *
	 * @dataProvider licenseCallbacks
	 */
	public function testShouldReturnExistingHtmlUnchanged( $callback, $dependency, $method ) {
		$this->$dependency->shouldReceive( $method )->never();

		$this->assertSame( '<div>first</div>', $this->subscriber->$callback( '<div>first</div>' ) );
	}
}
