<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\RecommendationOverride;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use Mockery\MockInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\RecommendationOverride;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RecommendationOverride::should_remove
 *
 * @group RocketCDN
 * @group Recommendations
 */
class Test_ShouldRemove extends TestCase {

	/**
	 * CDN context mock.
	 *
	 * @var Context|MockInterface
	 */
	private $context;

	/**
	 * Options mock.
	 *
	 * @var Options_Data|MockInterface
	 */
	private $options;

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

		$this->context  = Mockery::mock( Context::class );
		$this->options  = Mockery::mock( Options_Data::class );
		$this->override = new RecommendationOverride( $this->context, Mockery::mock( SubscriptionController::class ), $this->options );
	}

	/**
	 * Test that the recommendation is removed only when the display filter is off or another CDN is in use.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected result.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Filters\expectApplied( 'rocket_display_rocketcdn_recommendation' )
			->once()
			->andReturn( $config['display'] ?? true );

		$this->context->shouldReceive( 'get_cdn_state' )->andReturn( $config['cdn_state'] );
		$this->options->shouldReceive( 'get' )->with( 'cdn_cnames', [] )->andReturn( $config['cdn_cnames'] ?? [] );

		$this->assertSame( $expected, $this->override->should_remove( [ 'option_slug' => 'plugin_rocketcdn' ] ) );
	}
}
