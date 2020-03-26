<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_DisableOptionsOnAmp extends TestCase {
	private $amp;
	private $options;

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->amp     = new AMP( $this->options );
	}

	public function testShouldBailoutIfIsNotAmpEndpoint() {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( false );

		Functions\expect( 'remove_filter' )->never();
		$this->options->shouldReceive( 'get' )->never();

		$this->amp->disable_options_on_amp();
	}

	public function testShouldDisableOptionForAmpExceptImageSrcSet() {
		global $wp_filter;
		add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		$wp_filter = [ 'rocket_buffer' => '__return_true' ];
		add_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

		// Check the hooks before invoking the method.
		$this->assertTrue( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertTrue( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );

		Functions\expect( 'is_amp_endpoint' )->once()->andReturn( true );
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'do_cloudflare', 0 )
		              ->andReturn( 0 );
		$this->options->shouldReceive( 'get' )->with( 'cloudflare_protocol_rewrite', 0 )->never();
		Filters\expectApplied( 'do_rocket_protocol_rewrite' )->with( false )->never();

		// Run it.
		$this->amp->disable_options_on_amp();

		// Check the hooks after invoking the method.
		$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertTrue( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertEmpty( $wp_filter );
		$this->assertTrue( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );
	}

	public function testShouldDisableOptionForAmpWhenCloudflareEnabled() {
		global $wp_filter;
		add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		$wp_filter = [ 'rocket_buffer' => '__return_true' ];
		add_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

		// Check the hooks before invoking the method.
		$this->assertTrue( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertTrue( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );

		Functions\expect( 'is_amp_endpoint' )->once()->andReturn( true );
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'do_cloudflare', 0 )
		              ->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'cloudflare_protocol_rewrite', 0 )
		              ->andReturn( 1 );
		Filters\expectApplied( 'do_rocket_protocol_rewrite' )->with( false )->never();

		// Run it.
		$this->amp->disable_options_on_amp();

		// Check the hooks after invoking the method.
		$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertTrue( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertEmpty( $wp_filter );
		$this->assertFalse( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );
	}

	public function testShouldDisableOptionForAmpWhenCloudflareEnabledAndFilterProtocolRewrite() {
		global $wp_filter;
		add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
		$wp_filter = [ 'rocket_buffer' => '__return_true' ];
		add_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

		// Check the hooks before invoking the method.
		$this->assertTrue( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertTrue( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );

		Functions\expect( 'is_amp_endpoint' )->once()->andReturn( true );
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'do_cloudflare', 0 )
		              ->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
		              ->once()
		              ->with( 'cloudflare_protocol_rewrite', 0 )
		              ->andReturn( 0 );
		Filters\expectApplied( 'do_rocket_protocol_rewrite' )->once()->with( false )->andReturn( true );

		// Run it.
		$this->amp->disable_options_on_amp();

		// Check the hooks after invoking the method.
		$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 ) );
		$this->assertTrue( has_filter( 'do_rocket_lazyload', '__return_false' ) );
		$this->assertEmpty( $wp_filter );
		$this->assertFalse( has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX ) );
	}
}
