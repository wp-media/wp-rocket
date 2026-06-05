<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Context::is_rocketcdn_paused
 * @group  CDN
 * @group  RocketCDN
 */
class Test_IsRocketcdnPaused extends TestCase {

	private $options;
	private $subscription_controller;
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
	public function testShouldReturnExpectedValue( array $config, bool $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'rocketcdn', false )
			->andReturn( $config['rocketcdn'] );

		$this->assertSame( $expected, $this->context->is_rocketcdn_paused() );
	}
}
