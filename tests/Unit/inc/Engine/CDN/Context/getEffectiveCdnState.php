<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Context::get_effective_cdn_state
 * @group  CDN
 */
class Test_GetEffectiveCdnState extends TestCase {
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
	public function testShouldReturnExpectedState( array $config, string $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn'] ?? 1 );

		if ( empty( $config['cdn'] ?? 1 ) ) {
			// cdn = 0 short-circuits before ever reading cdn_type or cdn_state.
			$this->options->shouldNotReceive( 'get' )->with( 'cdn_type', Mockery::any() );
			$this->options->shouldNotReceive( 'get' )->with( 'cdn_state', Mockery::any() );
			$this->subscription_controller->shouldNotReceive( 'has_token' );

			$this->assertSame( $expected, $this->context->get_effective_cdn_state() );
			return;
		}

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', Context::ROCKETCDN_TYPE )
			->andReturn( $config['cdn_type'] ?? Context::ROCKETCDN_TYPE );

		if ( Context::ROCKETCDN_TYPE !== ( $config['cdn_type'] ?? Context::ROCKETCDN_TYPE ) ) {
			// Non-rocketcdn cdn_type short-circuits before ever reading cdn_state.
			$this->options->shouldNotReceive( 'get' )->with( 'cdn_state', Mockery::any() );
			$this->subscription_controller->shouldNotReceive( 'has_token' );

			$this->assertSame( $expected, $this->context->get_effective_cdn_state() );
			return;
		}

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_state', Context::CDN_STATE_NOTHING )
			->andReturn( $config['cdn_state'] );

		if ( Context::ROCKETCDN_FREE_TYPE === $config['cdn_state'] ) {
			$this->subscription_controller->shouldReceive( 'has_token' )
				->once()
				->andReturn( $config['has_token'] ?? false );
		} else {
			$this->subscription_controller->shouldNotReceive( 'has_token' );
		}

		$this->assertSame( $expected, $this->context->get_effective_cdn_state() );
	}
}
