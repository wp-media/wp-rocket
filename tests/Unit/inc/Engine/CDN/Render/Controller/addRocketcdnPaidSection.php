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
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( true );

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

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( false );
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
		$this->assertArrayHasKey( 'toggle_tooltip', $sections['rocketcdn_paid_section'] );
		$this->assertSame( '', $sections['rocketcdn_paid_section']['toggle_tooltip'] );
		// Genuinely active: status text must not read as paused (green circle/active text).
		$this->assertFalse( $sections['rocketcdn_paid_section']['status_indicator']['is_paused'] );
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

	/**
	 * While a subscription is being created and nothing else is forcing the section off,
	 * show_pause_state() (unlike should_reject_rocketcdn_activation()) doesn't treat
	 * is_subscription_loading() as a pause reason on its own, so the "Creating your
	 * subscription..." text set earlier in get_status_indicator_data() must survive rather
	 * than being overwritten by the generic paused text.
	 *
	 * @return void
	 */
	public function testShouldNotOverwriteLoadingTextWithPausedTextWhileSubscriptionLoading(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'has_inactive_subscription' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_free' )
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

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( false );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$status_indicator = $sections['rocketcdn_paid_section']['status_indicator'];

		$this->assertFalse( $status_indicator['is_paused'] );
		$this->assertSame( 'Creating your subscription...', $status_indicator['status_text'] );
		$this->assertSame( 'Please wait, RocketCDN will be ready in about 30s.', $status_indicator['details'] );
		$this->assertStringNotContainsString( 'wpr-cdn-status--paused', $status_indicator['class'] );
	}

	/**
	 * Surfaces the banned-reseller tooltip alongside is_forced_off for the paid section.
	 *
	 * Also covers the Test Findings doc's "Green circle and active text is there for Pro
	 * while being in grace period"-style reports: the status indicator must show as paused
	 * rather than active whenever the toggle itself is forced off, even though is_forced_off()
	 * alone misses a paid-tier ban (see should_reject_rocketcdn_activation()'s docblock).
	 *
	 * @return void
	 */
	public function testShouldSurfaceBannedTooltipWhenResellerLicenseBanned(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		// get_applied_cdn_state() only ever resolves to CDN_STATE_NOTHING, ROCKETCDN_TYPE or
		// BYOCDN_TYPE (see Context::get_applied_cdn_state()) - the free/paid tier is
		// get_rocketcdn_state()'s job.
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_inactive_subscription' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( false );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( true );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$this->assertTrue( $sections['rocketcdn_paid_section']['is_forced_off'] );
		$this->assertSame(
			'Contact support to find out how to restore access.',
			$sections['rocketcdn_paid_section']['toggle_tooltip']
		);
		$this->assertTrue( $sections['rocketcdn_paid_section']['status_indicator']['is_paused'] );
		$this->assertSame(
			'RocketCDN is paused',
			$sections['rocketcdn_paid_section']['status_indicator']['status_text']
		);
	}

	/**
	 * Forces the paid toggle off, with the forced-paused tooltip, when the paid
	 * subscription itself is cancelled - should_reject_rocketcdn_activation() missed
	 * this until it also checked is_forced_off().
	 *
	 * Also covers Test Findings: "After cancel Paid and delete website, free status in
	 * cdn tab is active while it shouldn't" - the status indicator must flip to paused
	 * once the subscription is cancelled outside the grace period, not just the toggle.
	 *
	 * @return void
	 */
	public function testShouldForceOffWhenPaidSubscriptionCancelled(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		// get_applied_cdn_state() only ever resolves to CDN_STATE_NOTHING, ROCKETCDN_TYPE or
		// BYOCDN_TYPE (see Context::get_applied_cdn_state()) - the free/paid tier is
		// get_rocketcdn_state()'s job.
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
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
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( true );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( false );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$this->assertTrue( $sections['rocketcdn_paid_section']['is_forced_off'] );
		$this->assertSame(
			'Renew to use RocketCDN Free.',
			$sections['rocketcdn_paid_section']['toggle_tooltip']
		);
		// The stored state is still 'rocketcdn_paid', but the toggle must show off rather than
		// checked-but-disabled - is_forced_paused() already stops CDN delivery on the front end
		// via maybe_pause_cdn_for_inactive_subscription().
		$this->assertFalse( $sections['rocketcdn_paid_section']['is_active'] );
		$this->assertTrue( $sections['rocketcdn_paid_section']['status_indicator']['is_paused'] );
		$this->assertSame(
			'RocketCDN is paused',
			$sections['rocketcdn_paid_section']['status_indicator']['status_text']
		);
	}

	/**
	 * Forces the paid toggle off while the cancelled subscription is still within its
	 * grace period - is_forced_paused()'s first branch (is_paid() && is_in_grace_period())
	 * covers this before the subscription is fully cancelled outside the grace period.
	 *
	 * Directly covers Test Findings: "Green circle and active text is there for Pro while
	 * being in grace period" - expected an orange circle / paused text instead.
	 *
	 * @return void
	 */
	public function testShouldForceOffWhenPaidSubscriptionCancelledWithinGracePeriod(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_PAID_TYPE );

		// get_applied_cdn_state() only ever resolves to CDN_STATE_NOTHING, ROCKETCDN_TYPE or
		// BYOCDN_TYPE (see Context::get_applied_cdn_state()) - the free/paid tier is
		// get_rocketcdn_state()'s job.
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
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
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( false );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( false );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_paid_section( [] );

		$this->assertTrue( $sections['rocketcdn_paid_section']['is_forced_off'] );
		$this->assertSame(
			'Renew to use RocketCDN Free.',
			$sections['rocketcdn_paid_section']['toggle_tooltip']
		);
		$this->assertFalse( $sections['rocketcdn_paid_section']['is_active'] );
		$this->assertTrue( $sections['rocketcdn_paid_section']['status_indicator']['is_paused'] );
		$this->assertSame(
			'RocketCDN is paused',
			$sections['rocketcdn_paid_section']['status_indicator']['status_text']
		);
	}
}
