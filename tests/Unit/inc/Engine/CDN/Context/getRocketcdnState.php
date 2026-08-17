<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetRocketcdnState extends TestCase {
	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Context
	 */
	private $context;

	public function set_up() {
		parent::set_up();

		$this->options                 = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->context                 = new Context( $this->options, $this->subscription_controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedRocketcdnState( array $config, string $expected ) {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $config['is_subscription_creation_loading'] ?? false );

		if ( $config['is_subscription_creation_loading'] ?? false ) {
			$this->assertSame( $expected, $this->context->get_rocketcdn_state() );

			return;
		}

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( $config['is_in_grace_period'] ?? false );

		if ( $config['is_in_grace_period'] ?? false ) {
			$this->assertSame( $expected, $this->context->get_rocketcdn_state() );

			return;
		}

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $config['has_active_subscription'] ?? false );

		if ( $config['has_active_subscription'] ?? false ) {
			$this->subscription_controller->shouldReceive( 'is_paid' )
				->andReturn( $config['is_paid'] ?? false );

			if ( ! ( $config['is_paid'] ?? false ) ) {
				$this->subscription_controller->shouldReceive( 'is_free' )
					->andReturn( $config['is_free'] ?? false );
			}
		}

		$this->assertSame( $expected, $this->context->get_rocketcdn_state() );
	}
}
