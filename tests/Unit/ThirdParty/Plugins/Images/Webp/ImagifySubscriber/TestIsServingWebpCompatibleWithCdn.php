<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\ImagifySubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Imagify_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestIsServingWebpCompatibleWithCdn extends TestCase {
	/**
	 * Test Imagify_Subscriber->is_serving_webp_compatible_with_cdn() should return false when Imagify is not enabled.
	 */
	public function testShouldReturnFalseWhenImagifyNotEnabled() {
		Functions\expect( 'get_imagify_option' )
			->once()
			->andReturn( false );

		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	/**
	 * Test Imagify_Subscriber->is_serving_webp() should return false when display option is disabled.
	 */
	public function testShouldReturnFalseWhenDisplayOptionIsDisabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new Imagify_Subscriber( $optionsData );

		Functions\expect( 'get_imagify_option' )
			->once()
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

	/**
	 * Test Imagify_Subscriber->is_serving_webp() should return false when the .htaccess rewrite rules method is enabled.
	 */
	public function testShouldReturnTrueWhenRewriteIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
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

	/**
	 * Test Imagify_Subscriber->is_serving_webp() should return true when the <picture> method is enabled.
	 */
	public function testShouldReturnTrueWhenPictureIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
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
