<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use Mockery;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::set_cdn_zone
 *
 * @group RocketCDN
 */
class Test_SetCdnZone extends TestCase {

	private $context;
	private $subscription_controller;

	/**
	 * @var FrontendSubscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->context    = Mockery::mock( Context::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->subscriber = new FrontendSubscriber( $this->context, $this->subscription_controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedZone( $config, $expected ) {
		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( 'rocketcdn' === $config['cdn_type'] );

		if ( 'rocketcdn' === $config['cdn_type'] ) {
			$this->subscription_controller->shouldReceive( 'has_active_subscription' )
				->andReturn( $config['has_active_subscription'] );
			if ($config['has_active_subscription'] ) {
				$this->subscription_controller->shouldReceive( 'get_rocketcdn_url' )
					->andReturn( $config['cdn_url'] );
			}
		}

		$result = $this->subscriber->set_cdn_zone( null );

		$this->assertSame( $expected['cdn_zone'], $result );
	}
}
