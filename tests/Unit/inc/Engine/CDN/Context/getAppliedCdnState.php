<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetAppliedCdnState extends TestCase {
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
	public function testShouldReturnExpectedAppliedState( array $config, string $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_byocdn_enabled', 0 )
			->andReturn( $config['cdn_byocdn_enabled'] ?? 0 );

		if ( empty( $config['cdn_byocdn_enabled'] ) ) {
			$this->options->shouldReceive( 'get' )
				->with( 'rocketcdn_free_enabled', 0 )
				->andReturn( $config['rocketcdn_free_enabled'] ?? 0 );

			if ( empty( $config['rocketcdn_free_enabled'] ) ) {
				$this->options->shouldReceive( 'get' )
					->with( 'rocketcdn_pro_enabled', 0 )
					->andReturn( $config['rocketcdn_pro_enabled'] ?? 0 );
			}
		}

		$this->assertSame( $expected, $this->context->get_applied_cdn_state() );
	}
}
