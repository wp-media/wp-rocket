<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\FrontendSubscriber;

use Mockery;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\FrontendSubscriber::maybe_pause_resume_rocketcdn
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybePauseResumeRocketcdn extends TestCase {

	private $context;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->context    = Mockery::mock( Context::class );
		$subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->subscriber = new FrontendSubscriber( $this->context, $subscription_controller );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( array $config, $expected ) {
		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( $config['is_rocketcdn'] );

		if ( $config['is_rocketcdn'] ) {
			$this->context->shouldReceive( 'is_rocketcdn_paused' )
				->andReturn( $config['is_rocketcdn_paused'] );
		}

		$this->assertSame( $expected, $this->subscriber->maybe_pause_resume_rocketcdn( $config['value'] ) );
	}
}
