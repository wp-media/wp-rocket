<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_paid_section
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_paid_section
 * @group  CDN
 * @group  RocketCDN
 */
class Test_AddRocketcdnPaidSection extends TestCase {

	/**
	 * Beacon mock instance.
	 *
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * CDN Context mock instance.
	 *
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * Options_Data mock instance.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * Options API mock instance.
	 *
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * RocketCDNQuery mock instance.
	 *
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

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
	 * Cache mock instance.
	 *
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->stubTranslationFunctions();

		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->context                 = Mockery::mock( Context::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->cdn_query               = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->cache                   = Mockery::mock( Cache::class );

		Functions\when( 'get_option' )->justReturn( [ 'persistent' => false ] );
	}

	/**
	 * Creates a Controller instance under test.
	 *
	 * @return Controller
	 */
	private function get_controller(): Controller {
		return new Controller(
			$this->beacon,
			'',
			$this->context,
			$this->options,
			$this->options_api,
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	/**
	 * Sets up the common stubs shared by both scenarios.
	 *
	 * @return void
	 */
	private function stub_common_expectations(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_inactive_subscription' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( false );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );
	}

	/**
	 * Marks the section active when the applied RocketCDN state is the paid tier.
	 *
	 * @return void
	 */
	public function testShouldMarkActiveWhenRocketcdnStateIsPaid(): void {
		$this->stub_common_expectations();

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$this->assertArrayHasKey( 'rocketcdn_paid_section', $sections );
		$this->assertTrue( $sections['rocketcdn_paid_section']['is_active'] );
	}

	/**
	 * Marks the section inactive when the applied RocketCDN state isn't the paid tier.
	 *
	 * @return void
	 */
	public function testShouldMarkInactiveWhenRocketcdnStateIsNotPaid(): void {
		$this->stub_common_expectations();

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::CDN_STATE_NOTHING );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$this->assertArrayHasKey( 'rocketcdn_paid_section', $sections );
		$this->assertFalse( $sections['rocketcdn_paid_section']['is_active'] );
	}
}
