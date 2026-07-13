<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::maybe_display_rocketcdn_cta
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_display_rocketcdn_cta
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybeDisplayRocketcdnCta extends TestCase {

	/**
	 * SubscriptionController mock instance.
	 *
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * User mock instance.
	 *
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * Controller instance under test.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$beacon         = Mockery::mock( Beacon::class );
		$context        = Mockery::mock( Context::class );
		$options        = Mockery::mock( Options_Data::class );
		$cdn_query      = $this->createMock( RocketCDNQuery::class );
		$cache          = Mockery::mock( Cache::class );

		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );

		$this->controller = new Controller(
			$beacon,
			'',
			$context,
			$options,
			$cdn_query,
			$this->subscription_controller,
			$this->user,
			$cache
		);
	}

	/**
	 * Tests that maybe_display_rocketcdn_cta() returns false for a reseller account.
	 *
	 * @return void
	 */
	public function testShouldReturnFalseForResellerAccount(): void {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->once()
			->andReturn( false );

		$this->user->shouldReceive( 'is_reseller_account' )
			->once()
			->andReturn( true );

		$result = $this->controller->maybe_display_rocketcdn_cta( true );

		$this->assertFalse( $result );
	}

	/**
	 * Tests that maybe_display_rocketcdn_cta() returns true for a non-reseller account.
	 *
	 * @return void
	 */
	public function testShouldReturnTrueForNonResellerAccount(): void {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->once()
			->andReturn( false );

		$this->user->shouldReceive( 'is_reseller_account' )
			->once()
			->andReturn( false );

		$result = $this->controller->maybe_display_rocketcdn_cta( true );

		$this->assertTrue( $result );
	}

	/**
	 * Tests that maybe_display_rocketcdn_cta() returns false when display is false regardless of reseller status.
	 *
	 * @return void
	 */
	public function testShouldReturnFalseWhenDisplayIsFalse(): void {
		$result = $this->controller->maybe_display_rocketcdn_cta( false );

		$this->assertFalse( $result );
	}

	/**
	 * Tests that maybe_display_rocketcdn_cta() returns false when subscription is loading.
	 *
	 * @return void
	 */
	public function testShouldReturnFalseWhenSubscriptionLoading(): void {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->once()
			->andReturn( true );

		$result = $this->controller->maybe_display_rocketcdn_cta( true );

		$this->assertFalse( $result );
	}
}
