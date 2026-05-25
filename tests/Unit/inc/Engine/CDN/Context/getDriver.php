<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Context;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

class Test_GetDriver extends TestCase {
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
	public function testShouldReturnExpectedDriver( array $config, string $expected ) {
		$is_loading = $config['is_loading'] ?? false;
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $is_loading );

		$cdn_type = $config['cdn_type'] ?? '';
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( $cdn_type );

		if ( 'rocketcdn' === $cdn_type && ! $is_loading ) {
			$this->subscription_controller->shouldReceive( 'has_active_subscription' )
				->andReturn( $config['has_active_subscription'] ?? false );

			if ( ! empty( $config['has_active_subscription'] ) ) {
				$this->subscription_controller->shouldReceive( 'is_paid' )
					->andReturn( $config['is_paid'] ?? false );
			}
		}

		$this->assertSame( $expected, $this->context->get_driver() );
	}
}
