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
 * Test class covering the private \WP_Rocket\Engine\CDN\Render\Controller::get_rocketcdn_toggle_forced_off_tooltip
 * method, which has no public accessor of its own. Exercised through
 * add_rocketcdn_free_section() for free-tier scenarios and
 * add_rocketcdn_paid_section() for paid-tier ones (e.g. a cancelled paid
 * plan) - is_paid() gates which of the two builders actually sets a
 * 'toggle_tooltip' key at all, so the scenario being tested decides which
 * builder can be used to observe it.
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_free_section
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_paid_section
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetRocketcdnToggleForcedOffTooltip extends TestCase {

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
		$this->cdn_query               = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->cache                   = Mockery::mock( Cache::class );
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
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	/**
	 * Runs the section builder matching the scenario's tier - add_rocketcdn_paid_section()
	 * when is_paid is true, add_rocketcdn_free_section() otherwise - since is_paid()
	 * gates which of the two ever sets a 'toggle_tooltip' key: each returns its
	 * input untouched for the tier it doesn't handle (see is_paid() checks in
	 * Controller::add_rocketcdn_free_section() / add_rocketcdn_paid_section()).
	 *
	 * @param array $config Scenario configuration.
	 *
	 * @return array
	 */
	private function build_section( array $config ): array {
		$this->context->shouldReceive( 'get_driver' )->andReturn( Context::ROCKETCDN_TYPE );
		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::CDN_STATE_NOTHING );
		$this->context->shouldReceive( 'get_rocketcdn_state' )->andReturn( Context::CDN_STATE_NOTHING );
		$this->context->shouldReceive( 'get_free_page_limit' )->andReturn( 3 );
		$this->context->shouldReceive( 'is_rocketcdn' )->andReturn( $config['is_rocketcdn'] ?? true );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn_free' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $config['is_subscription_loading'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( $config['is_free'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $config['has_active_subscription'] ?? false );

		$is_paid = $config['is_paid'] ?? false;

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $is_paid );

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( $config['is_in_grace_period'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( $config['is_cancelled_outside_grace_period'] ?? false );

		$this->user->shouldReceive( 'is_reseller_account' )->andReturn( false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( $config['is_reseller_license_banned'] ?? false );

		$this->options->shouldReceive( 'get' )->with( 'cdn' )->andReturn( true );

		$this->cdn_query->method( 'query' )->willReturn( [] );

		if ( $is_paid ) {
			return $this->get_controller()->add_rocketcdn_paid_section( [] )['rocketcdn_paid_section'];
		}

		return $this->get_controller()->add_rocketcdn_free_section( [] )['rocketcdn_free_section'];
	}

	/**
	 * Tests that the toggle_tooltip key holds the expected copy for each scenario.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config           Scenario configuration.
	 * @param string $expected_tooltip Expected tooltip string.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedTooltip( array $config, string $expected_tooltip ): void {
		$section = $this->build_section( $config );

		$this->assertSame( $expected_tooltip, $section['toggle_tooltip'] );

		// The invariant this depends on: the tooltip and is_forced_off can never
		// disagree, because both are derived from the same precedence chain.
		$this->assertSame( '' !== $expected_tooltip, $section['is_forced_off'] );
	}

	/**
	 * Data provider for testShouldReturnExpectedTooltip.
	 *
	 * @return array
	 */
	public function configTestData(): array {
		return [
			'empty when nothing forces it off'      => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => false,
				],
				'',
			],
			'activation-in-progress copy first'     => [
				[
					'is_subscription_loading' => true,
				],
				'RocketCDN is currently being activated. Please wait, this should only take a moment.',
			],
			'expired-licence copy second'           => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => true,
				],
				'RocketCDN is currently paused because your WP Rocket licence has expired.',
			],
			'banned-reseller copy third'            => [
				[
					'is_subscription_loading'    => false,
					'is_rocketcdn'               => true,
					'is_free'                    => true,
					'is_license_invalid'         => false,
					'is_reseller_license_banned' => true,
				],
				'RocketCDN is currently paused because your WP Rocket licence has been banned.',
			],
			'loading takes precedence over expired' => [
				[
					'is_subscription_loading' => true,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => true,
				],
				'RocketCDN is currently being activated. Please wait, this should only take a moment.',
			],
			// should_display_licence_expired_notice() deliberately excludes banned
			// resellers (`! is_reseller_license_banned()`), so a banned + invalid
			// licence always falls through to the banned copy, never the expired
			// one - same precedent as should_reject_rocketcdn_activation().
			'banned wins even when licence is also invalid' => [
				[
					'is_subscription_loading'    => false,
					'is_rocketcdn'               => true,
					'is_free'                    => true,
					'is_license_invalid'         => true,
					'is_reseller_license_banned' => true,
				],
				'RocketCDN is currently paused because your WP Rocket licence has been banned.',
			],
			'forced-paused copy fourth, for a cancelled paid plan' => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => false,
					'is_license_invalid'      => false,
					'is_paid'                 => true,
					'is_in_grace_period'      => true,
				],
				'RocketCDN is currently paused because your subscription is no longer active.',
			],
		];
	}
}
