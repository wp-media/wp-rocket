<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetCdnStatus extends TestCase {
	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Context
	 */
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options                 = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->context                 = new Context( $this->options, $this->subscription_controller, $this->user );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedCdnStatus( array $config, string $expected ) {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $config['is_subscription_creation_loading'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $config['is_paid'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( $config['is_in_grace_period'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( $config['is_cancelled_outside_grace_period'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( $config['is_free'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );
		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( $config['is_reseller_license_banned'] ?? false );
		$this->user->shouldReceive( 'is_revoked' )
			->andReturn( $config['is_revoked'] ?? false );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_state', Context::CDN_STATE_NOTHING )
			->andReturn( $config['cdn_state'] ?? Context::CDN_STATE_NOTHING );

		$this->assertSame( $expected, $this->context->get_cdn_status() );
	}
}
