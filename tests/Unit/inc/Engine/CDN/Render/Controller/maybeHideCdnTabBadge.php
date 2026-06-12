<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::maybe_hide_cdn_tab_badge
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_hide_cdn_tab_badge
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybeHideCdnTabBadge extends TestCase {

	/**
	 * SubscriptionController mock instance.
	 *
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

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

		$beacon                        = Mockery::mock( Beacon::class );
		$context                       = Mockery::mock( Context::class );
		$options                       = Mockery::mock( Options_Data::class );
		$cdn_query                     = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$user                          = Mockery::mock( User::class );

		$this->controller = new Controller(
			$beacon,
			'',
			$context,
			$options,
			$cdn_query,
			$this->subscription_controller,
			$user
		);
	}

	/**
	 * Tests that maybe_hide_cdn_tab_badge returns the expected badge value.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Test configuration.
	 * @param string $expected Expected badge value.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedBadge( array $config, string $expected ): void {
		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $config['is_paid'] );

		$result = $this->controller->maybe_hide_cdn_tab_badge( $config['badge'] );

		$this->assertSame( $expected, $result );
	}
}
