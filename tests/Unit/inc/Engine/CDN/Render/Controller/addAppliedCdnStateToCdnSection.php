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
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::add_applied_cdn_state_to_cdn_section
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_applied_cdn_state_to_cdn_section
 * @group  CDN
 */
class Test_AddAppliedCdnStateToCdnSection extends TestCase {

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
	 * Adds applied_cdn_state to the cdn_section entry when it's present.
	 *
	 * @return void
	 */
	public function testShouldAddAppliedCdnStateWhenCdnSectionExists(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->once()
			->andReturn( Context::BYOCDN_TYPE );

		$controller = $this->get_controller();
		$sections   = $controller->add_applied_cdn_state_to_cdn_section(
			[
				'cdn_section' => [
					'title' => 'Your CDN',
				],
			]
		);

		$this->assertSame( Context::BYOCDN_TYPE, $sections['cdn_section']['applied_cdn_state'] );
		$this->assertTrue( $sections['cdn_section']['is_active'] );
	}

	/**
	 * Marks is_active false when the applied CDN state isn't BYOCDN.
	 *
	 * @return void
	 */
	public function testShouldMarkInactiveWhenAppliedCdnStateIsNotByocdn(): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->once()
			->andReturn( Context::ROCKETCDN_FREE_TYPE );

		$controller = $this->get_controller();
		$sections   = $controller->add_applied_cdn_state_to_cdn_section(
			[
				'cdn_section' => [
					'title' => 'Your CDN',
				],
			]
		);

		$this->assertFalse( $sections['cdn_section']['is_active'] );
	}

	/**
	 * Leaves sections untouched when there's no cdn_section entry to add to.
	 *
	 * @return void
	 */
	public function testShouldReturnSectionsUnchangedWhenCdnSectionMissing(): void {
		$this->context->shouldNotReceive( 'get_applied_cdn_state' );

		$controller = $this->get_controller();
		$sections   = $controller->add_applied_cdn_state_to_cdn_section( [ 'other_section' => [] ] );

		$this->assertSame( [ 'other_section' => [] ], $sections );
	}
}
