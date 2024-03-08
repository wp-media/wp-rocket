<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\Imagify_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber::is_serving_webp_compatible_with_cdn
 * @group  ThirdParty
 * @group  Webp
 */
class Test_IsServingWebpCompatibleWithCdn extends TestCase {

	public function testShouldReturnFalseWhenImagifyNotEnabled() {
		Functions\expect( 'get_imagify_option' )
			->twice()
			->andReturn( false );

		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnFalseWhenDisplayOptionIsDisabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->twice()
			->andReturnUsing(
				function( $option_name ) {
					if ( 'display_webp' === $option_name ) {
						return 0;
					}
					if ( 'display_webp_method' === $option_name ) {
						return 'picture';
					}

					return false;
				}
			);

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnTrueWhenRewriteIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->twice()
			->andReturnUsing(
				function( $option_name ) {
					if ( 'display_webp' === $option_name ) {
						return 1;
					}
					if ( 'display_webp_method' === $option_name ) {
						return 'rewrite';
					}

					return false;
				}
			);

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnTrueWhenPictureIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->twice()
			->andReturnUsing(
				function( $option_name ) {
					if ( 'display_webp' === $option_name ) {
						return 1;
					}
					if ( 'display_webp_method' === $option_name ) {
						return 'picture';
					}

					return false;
				}
			);

		$this->assertTrue( $subscriber->is_serving_webp_compatible_with_cdn() );
	}
}
