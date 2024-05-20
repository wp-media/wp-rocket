<?php

namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Images\Webp\EwwwSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber::is_serving_webp_compatible_with_cdn
 * @group  ThirdParty
 */
class Test_IsServingWebpCompatibleWithCdn extends TestCase {

	public function testShouldReturnTrueWhenExactdnIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnTrueWhenJsRewriteIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturnUsing( function( $option_name ) {
				return 'ewww_image_optimizer_webp_for_cdn' === $option_name;
			} );

		$this->assertTrue( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnTrueWhenHtaccessRewriteRewriteRulesAreEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturn( false );

		Functions\when( 'get_home_path' );
		Functions\when( 'extract_from_markers' );
		Functions\when( 'ewww_image_optimizer_webp_rewrite_verify' );

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}

	public function testShouldReturnFalseWhenNothingEnabledInEwww() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturn( false );

		Functions\when( 'get_home_path' );
		Functions\when( 'extract_from_markers' );
		Functions\expect( 'ewww_image_optimizer_webp_rewrite_verify' )
			->andReturn( 'random rewrite rules' );

		$this->assertFalse( $subscriber->is_serving_webp_compatible_with_cdn() );
	}
}
