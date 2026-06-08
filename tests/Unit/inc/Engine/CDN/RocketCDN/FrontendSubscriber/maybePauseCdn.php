<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use Mockery;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::maybe_pause_cdn
 *
 * @group RocketCDN
 */
class MaybePauseCdn extends TestCase {

	private $context;
	private $subscription_controller;

	/**
	 * @var FrontendSubscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->context                 = Mockery::mock( Context::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->subscriber = new FrontendSubscriber( $this->context, $this->subscription_controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( 'rocketcdn' === $config['cdn_type'] );

		$result = $this->subscriber->maybe_pause_cdn( $config['value'] );

		$this->assertSame( $expected['cdn_state'], $result );
	}
}
