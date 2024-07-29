<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::disable_options_on_amp
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_DisableOptionsOnAmp extends TestCase {
	private $amp;
	private $options;
	private $cdn_subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->options        = Mockery::mock( Options_Data::class );
		$this->cdn_subscriber = Mockery::mock( Subscriber::class );
		$this->amp            = new AMP( $this->options, $this->cdn_subscriber );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\expect( 'is_amp_endpoint' )
			->once()
			->andReturn( $config['is_amp_endpoint'] );
		Functions\expect( 'is_singular' )
			->atMost()->once()
			->with( 'web-story')
			->andReturn( ! empty( $config['is-web-story'] ) );
		Functions\expect( 'is_embed' )
			->atMost()->once()
			->andReturn( false );
		Functions\expect( 'post_password_required' )
			->atMost()->once()
			->andReturn( false );

		if ( $expected[ 'bailout' ] ) {
			Functions\expect( 'remove_filter' )->never();
			$this->options->shouldReceive( 'get' )->never();
		} else {
			global $wp_filter;
			add_filter( 'wp_resource_hints', 'rocket_dns_prefetch', 10, 2 );
			$wp_filter = [ 'rocket_buffer' => '__return_true' ];
			add_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset', PHP_INT_MAX );

			// Check the hooks before invoking the method.
			$this->assertSame(
				10,
				has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' )
			);
			$this->assertFalse( has_filter( 'do_rocket_lazyload', '__return_false' ) );
			$this->assertFalse( has_filter( 'do_rocket_lazyload_iframes', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_async_css', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_delay_js', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_preload_links', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_minify_js', '__return_false' ) );
			$this->assertFalse( has_filter( 'pre_get_rocket_option_lazyload_css_bg_img', '__return_false' ) );

			$this->assertSame(
				PHP_INT_MAX,
				has_filter( 'wp_calculate_image_srcset', 'rocket_protocol_rewrite_srcset' )
			);
			$this->assertFalse( has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite' ] ) );
			$this->assertFalse( has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite_srcset' ] ) );

			Functions\expect( 'get_option' )
				->once()
				->with( 'amp-options', [] )
				->andReturn( $config[ 'amp_options' ]  );
		}

		$this->amp->disable_options_on_amp();

		if ( ! $expected[ 'bailout' ] ) {
			// Check the hooks after invoking the method.
			$this->assertFalse( has_filter( 'wp_resource_hints', 'rocket_dns_prefetch' ) );
			$this->assertSame(
				10,
				has_filter( 'do_rocket_lazyload', '__return_false' )
			);
			$this->assertSame(
				10,
				has_filter( 'do_rocket_lazyload_iframes', '__return_false' )
			);
			$this->assertSame(
				10,
				has_filter( 'pre_get_cloudflare_protocol_rewrite', '__return_false' )
			);
			$this->assertSame(
				10,
				has_filter( 'do_rocket_protocol_rewrite', '__return_false' )
			);
			$this->assertEmpty( $wp_filter );

			if ( in_array( $config[ 'amp_options' ][ 'theme_support' ], [ 'transitional', 'reader' ], true ) ) {
				$this->assertSame(
					10,
					has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite' ] )
				);
				$this->assertSame(
					10,
					has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite_srcset' ] )
				);
			} else {
				$this->assertFalse( has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite' ] ) );
				$this->assertFalse( has_filter( 'rocket_buffer', [ $this->cdn_subscriber, 'rewrite_srcset' ] ) );
			}
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'disableOptionsOnAmp' );
	}
}
