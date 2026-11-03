<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\RecommendationOverride;

use Brain\Monkey\Functions;
use Mockery;
use Mockery\MockInterface;
use ReflectionProperty;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\RecommendationOverride;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\ThirdParty\Hostings\HostResolver;
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

		Functions\stubEscapeFunctions();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'rocket_is_plugin_active' )->justReturn( false );

		$this->reset_host_cache();

		$this->context  = Mockery::mock( Context::class );
		$this->options  = Mockery::mock( Options_Data::class );
		$this->override = new RecommendationOverride( $this->context, Mockery::mock( SubscriptionController::class ), $this->options );
	}

	/**
	 * Tear down the test.
	 */
	public function tear_down() {
		unset( $_SERVER['GROUPONE_BRAND_NAME'] );
		$this->reset_host_cache();

		parent::tear_down();
	}

	/**
	 * Test that the recommendation is removed only when another CDN is in use or on one.com.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected result.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( isset( $config['brand'] ) ) {
			$_SERVER['GROUPONE_BRAND_NAME'] = $config['brand'];
		}

		$this->context->shouldReceive( 'get_cdn_state' )->andReturn( $config['cdn_state'] );
		$this->options->shouldReceive( 'get' )->with( 'cdn_cnames', [] )->andReturn( $config['cdn_cnames'] ?? [] );

		$this->assertSame( $expected, $this->override->should_remove( [ 'option_slug' => 'plugin_rocketcdn' ] ) );
	}

	/**
	 * Clears the static host cache of the resolver.
	 */
	private function reset_host_cache() {
		$property = new ReflectionProperty( HostResolver::class, 'hostname' );
		$property->setAccessible( true );
		$property->setValue( null, '' );
	}
}
