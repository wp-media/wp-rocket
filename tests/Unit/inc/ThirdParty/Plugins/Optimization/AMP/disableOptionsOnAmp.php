<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group ThirdParty
 * @group WithAmp
 */
class Test_DisableOptionsOnAmp extends TestCase {

	public function testShouldBailoutIfIsNotAmpEndpoint() {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( false );

		Functions\expect( 'remove_filter' )->never();

		$subscriber = new AMP( $this->createMock( Options_Data::class ) );
		$subscriber->disable_options_on_amp();
	}

	public function testShouldDisableOptionForAmpExceptImageSrcSet() {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( true );

		Functions\expect( 'remove_filter' )
			->once()
			->with( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'do_rocket_lazyload', '__return_false' );

		Functions\expect( 'apply_filters' )
			->once()
			->with(  'do_rocket_protocol_rewrite', false );

		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[ 'do_cloudflare', 0, 0, ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$subscriber = new AMP( $options );
		$subscriber->disable_options_on_amp();
	}

	public function testShouldDisableOptionForAmpWhenCloudflareEnabled() {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( true );

		Functions\expect( 'remove_filter' )
			->ordered()
			->once()
			->with( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 )
			->andAlsoExpectIt()
			->once()
			->with( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'do_rocket_lazyload', '__return_false' );

		Functions\expect( 'apply_filters' )
			->once()
			->with(  'do_rocket_protocol_rewrite', false );

		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[ 'do_cloudflare', 0, 1, ],
			[ 'cloudflare_protocol_rewrite', 0, 1, ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$subscriber = new AMP( $options );
		$subscriber->disable_options_on_amp();
	}

	public function testShouldDisableOptionForAmpWhenCloudflareDisabledButProtocolRewrite() {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( true );

		Functions\expect( 'remove_filter' )
			->ordered()
			->once()
			->with( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 )
			->andAlsoExpectIt()
			->once()
			->with( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

		Functions\expect( 'add_filter' )
			->once()
			->with( 'do_rocket_lazyload', '__return_false' );

		Functions\expect( 'apply_filters' )
			->once()
			->with(  'do_rocket_protocol_rewrite', false )
			->andReturn( true );

		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[ 'do_cloudflare', 0, 0, ],
			[ 'cloudflare_protocol_rewrite', 0, 0, ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		$subscriber = new AMP( $options );
		$subscriber->disable_options_on_amp();
	}
}
