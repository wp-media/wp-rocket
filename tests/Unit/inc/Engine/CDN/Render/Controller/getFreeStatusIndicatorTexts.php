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
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::get_free_status_indicator_texts
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::get_free_status_indicator_texts
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetFreeStatusIndicatorTexts extends TestCase {

	/**
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

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
	}

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

	private function default_texts(): array {
		return [
			'paused_status_text' => 'RocketCDN is paused',
			'active_status_text' => 'RocketCDN is active',
			'paused_details'     => 'RocketCDN is currently paused. Click Resume CDN to re-enable content delivery.',
			'status_text'        => '',
			'details'            => 'Start with your homepage.',
			'class'              => '',
		];
	}

	/**
	 * When $free is false the method returns the texts unchanged without touching context or subscription.
	 */
	public function testShouldReturnUnchangedWhenNotFree(): void {
		$texts = $this->default_texts();

		$result = $this->get_controller()->get_free_status_indicator_texts( $texts, 0, false, false );

		$this->assertSame( $texts, $result );
	}

	/**
	 * When CDN Free is active with no pages added and CDN is not paused,
	 * the default details text is kept unchanged.
	 */
	public function testShouldKeepDefaultDetailsWhenActiveAndNoPagesAdded(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )->andReturn( false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )->andReturn( false );

		$result = $this->get_controller()->get_free_status_indicator_texts( $this->default_texts(), 0, false, true );

		$this->assertSame( 'Start with your homepage.', $result['details'] );
		$this->assertArrayNotHasKey( 'no_status_indicator', $result );
	}

	/**
	 * When pages have been added, details is cleared and no_status_indicator is set so the
	 * status badge is hidden — the page list takes its place.
	 */
	public function testShouldSetNoStatusIndicatorAndClearDetailsWhenPagesExist(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )->andReturn( false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )->andReturn( false );

		$result = $this->get_controller()->get_free_status_indicator_texts( $this->default_texts(), 2, false, true );

		$this->assertTrue( $result['no_status_indicator'] );
		$this->assertSame( '', $result['details'] );
	}

	/**
	 * When the CDN is paused (applied state = CDN_STATE_NOTHING) and the user has an active
	 * subscription, the paused onboarding copy replaces the default details.
	 */
	public function testShouldSetPausedDetailsWhenCdnIsPaused(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::CDN_STATE_NOTHING );
		$this->subscription_controller->shouldReceive( 'has_active_subscription' )->andReturn( true );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )->andReturn( false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )->andReturn( false );

		$result = $this->get_controller()->get_free_status_indicator_texts( $this->default_texts(), 0, false, true );

		$this->assertSame(
			'<strong>Start with your homepages and add up to 2 more key pages.</strong> Includes unlimited traffic across 10 edge locations.',
			$result['details']
		);
	}

	/**
	 * When the WP Rocket licence is invalid, the expired CSS class is added and the details
	 * prompt the user to renew.
	 */
	public function testShouldSetExpiredClassAndDetailsWhenLicenceInvalid(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )->andReturn( Context::ROCKETCDN_FREE_TYPE );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )->andReturn( true );
		$this->user->shouldReceive( 'is_reseller_license_banned' )->andReturn( false );

		$result = $this->get_controller()->get_free_status_indicator_texts( $this->default_texts(), 0, false, true );

		$this->assertStringContainsString( 'wpr-cdn-status--expired', $result['class'] );
		$this->assertSame( 'Renew now to keep using RocketCDN Free.', $result['details'] );
	}
}
