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

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_state', Context::CDN_STATE_NOTHING )
			->andReturn( $config['cdn_state'] ?? 'nothing' );

		$this->assertSame( $expected, $this->context->get_rocketcdn_state() );
	}
}
