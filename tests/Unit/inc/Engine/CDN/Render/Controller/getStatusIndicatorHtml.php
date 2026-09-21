<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Brain\Monkey\Filters;
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
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::get_status_indicator_html
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::get_status_indicator_html
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetStatusIndicatorHtml extends TestCase {

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

	/**
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Test configuration.
	 * @param string $expected Expected return value.
	 */
	public function testShouldReturnExpectedHtml( array $config, string $expected ): void {
		$no_status_indicator = $config['no_status_indicator'];

		// Control no_status_indicator directly via the filter, isolating the guard
		// from the tier-specific callbacks that aren't registered in unit tests.
		Filters\expectApplied( 'rocket_rocketcdn_status_indicator_texts' )
			->once()
			->andReturnUsing(
				static function ( array $texts ) use ( $no_status_indicator ): array {
					$texts['no_status_indicator'] = $no_status_indicator;
					return $texts;
				}
			);

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $config['is_paid'] );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( false );

		// Minimal mocks for show_pause_state() internals — all default to the
		// non-paused, non-forced-off path so no_status_indicator alone drives the result.
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( false )->byDefault();

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( false );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( false );

		$result = $this->get_controller()->get_status_indicator_html( $config['pages_count'] );

		$this->assertSame( $expected, $result );
	}
}
