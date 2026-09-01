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
 * Test class covering the private \WP_Rocket\Engine\CDN\Render\Controller::get_forced_off_tooltip
 * method, exercised through add_rocketcdn_free_section() since it has no public
 * accessor of its own.
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_free_section
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetForcedOffTooltip extends TestCase {

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
	 * Runs add_rocketcdn_free_section() with the given scenario stubbed and
	 * returns the resulting section array.
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

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $config['is_subscription_loading'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( $config['is_free'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );

		$this->user->shouldReceive( 'is_reseller_account' )->andReturn( false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( $config['is_reseller_license_banned'] ?? false );

		$this->options->shouldReceive( 'get' )->with( 'cdn' )->andReturn( true );

		$this->cdn_query->method( 'query' )->willReturn( [] );

		return $this->get_controller()->add_rocketcdn_free_section( [] )['rocketcdn_free_section'];
	}

	/**
	 * Tests that get_forced_off_tooltip returns the expected copy for each scenario.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config            Scenario configuration.
	 * @param string $expected_tooltip  Expected tooltip string.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedTooltip( array $config, string $expected_tooltip ): void {
		$section = $this->build_section( $config );

		$this->assertSame( $expected_tooltip, $section['forced_off_tooltip'] );

		// The invariant this story depends on: the tooltip and is_forced_off can
		// never disagree, because both are derived from the same precedence chain.
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
				'RocketCDN is currently paused because your WPRocket licence has expired.',
			],
			'banned-reseller copy third'            => [
				[
					'is_subscription_loading'    => false,
					'is_rocketcdn'               => true,
					'is_free'                    => true,
					'is_license_invalid'         => false,
					'is_reseller_license_banned' => true,
				],
				'RocketCDN is currently paused because your WPRocket licence has been banned.',
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
			// one - this is the same precedent as should_reject_rocketcdn_activation().
			'banned wins even when licence is also invalid' => [
				[
					'is_subscription_loading'    => false,
					'is_rocketcdn'               => true,
					'is_free'                    => true,
					'is_license_invalid'         => true,
					'is_reseller_license_banned' => true,
				],
				'RocketCDN is currently paused because your WPRocket licence has been banned.',
			],
		];
	}
}
