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
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_free_section
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_rocketcdn_free_section
 * @group  CDN
 * @group  RocketCDN
 */
class Test_AddRocketcdnFreeSection extends TestCase {

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
	 * Tests that add_rocketcdn_free_section sets limit_reached correctly.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected value of limit_reached.
	 *
	 * @return void
	 */
	public function testShouldSetLimitReachedCorrectly( array $config, bool $expected ): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::CDN_STATE_NOTHING );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::CDN_STATE_NOTHING );

		$this->context->shouldReceive( 'get_free_page_limit' )
			->andReturn( 3 );

		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn_free' )
			->andReturn(
				[
					'id'  => 'beacon-id',
					'url' => 'https://example.com',
				]
			);

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_inactive_subscription' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( false );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );

		$this->user->shouldReceive( 'is_reseller_account' )
			->andReturn( false );

		$pages = array_fill(
			0,
			$config['page_count'],
			(object) [
				'id'    => 1,
				'url'   => 'http://example.org/',
				'title' => 'Page',
			]
		);

		$this->cdn_query->method( 'query' )
			->willReturn( $pages );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_free_section( [] );

		$this->assertArrayHasKey( 'rocketcdn_free_section', $sections );
		$this->assertArrayHasKey( 'cta_data', $sections['rocketcdn_free_section'] );
		$this->assertArrayHasKey( 'limit_reached', $sections['rocketcdn_free_section']['cta_data'] );
		$this->assertSame( $expected, $sections['rocketcdn_free_section']['cta_data']['limit_reached'] );
		$this->assertFalse( $sections['rocketcdn_free_section']['is_active'] );
	}

	/**
	 * Tests that add_rocketcdn_free_section marks the section active when the
	 * applied RocketCDN state is the free tier.
	 *
	 * @return void
	 */
	public function testShouldMarkActiveWhenRocketcdnStateIsFree(): void {
		$this->context->shouldReceive( 'get_driver' )
			->andReturn( Context::ROCKETCDN_TYPE );

		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( Context::ROCKETCDN_FREE_TYPE );

		$this->context->shouldReceive( 'get_rocketcdn_state' )
			->andReturn( Context::ROCKETCDN_FREE_TYPE );

		$this->context->shouldReceive( 'get_free_page_limit' )
			->andReturn( 3 );

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'rocketcdn_free' )
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
			->andReturn( true );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn' )
			->andReturn( true );

		$this->user->shouldReceive( 'is_reseller_account' )
			->andReturn( false );

		$this->cdn_query->method( 'query' )
			->willReturn( [] );

		$controller = $this->get_controller();
		$sections   = $controller->add_rocketcdn_free_section( [] );

		$this->assertTrue( $sections['rocketcdn_free_section']['is_active'] );
	}
}
