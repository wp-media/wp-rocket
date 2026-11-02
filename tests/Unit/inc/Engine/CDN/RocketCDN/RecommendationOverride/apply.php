<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\RecommendationOverride;

use Brain\Monkey\Functions;
use Mockery;
use Mockery\MockInterface;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\RecommendationOverride;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RecommendationOverride::apply
 *
 * @group RocketCDN
 * @group Recommendations
 */
class Test_Apply extends TestCase {

	/**
	 * CDN context mock.
	 *
	 * @var Context|MockInterface
	 */
	private $context;

	/**
	 * Subscription controller mock.
	 *
	 * @var SubscriptionController|MockInterface
	 */
	private $subscription_controller;

	/**
	 * Override under test.
	 *
	 * @var RecommendationOverride
	 */
	private $override;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$this->context                 = Mockery::mock( Context::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->override                = new RecommendationOverride( $this->context, $this->subscription_controller );
	}

	/**
	 * Test that the recommendation is adapted to the RocketCDN state as expected.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected recommendation.
	 */
	public function testShouldReturnExpectedRecommendation( $config, $expected ) {
		$this->context->shouldReceive( 'get_cdn_state' )->andReturn( $config['cdn_state'] );
		$this->subscription_controller->shouldReceive( 'get_express_checkout_url' )->andReturn( $config['express_url'] ?? '' );

		$this->assertSame( $expected, $this->override->apply( $config['recommendation'] ) );
	}
}
