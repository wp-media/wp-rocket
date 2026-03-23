<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\EWWW;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\EWWW;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\EWWW::is_serving_webp
 * @group  ThirdParty
 * @group  Webp
 */
class IsServingWebpTest extends TestCase {
	public function testShouldReturnTrueWhenExactdnIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_serving_webp() );
	}

	public function testShouldReturnTrueWhenJsRewriteIsEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturnUsing( function( $option_name ) {
				return 'ewww_image_optimizer_webp_for_cdn' === $option_name;
			} );

		$this->assertTrue( $subscriber->is_serving_webp() );
	}

	public function testShouldReturnTrueWhenHtaccessRewriteRewriteRulesAreEnabled() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturn( false );

		Functions\when( 'get_home_path' );
		Functions\when( 'extract_from_markers' );
		Functions\expect( 'ewww_image_optimizer_webp_rewrite_verify' )
			->once();

		Filters\expectApplied( 'rocket_webp_ewww_use_rewrite_rules' )
			->once()
			->with( true );

		$this->assertTrue( $subscriber->is_serving_webp() );
	}

	public function testShouldReturnFalseWhenNothingEnabledInEwww() {
		$optionsData = Mockery::mock( Options_Data::class );
		$subscriber  = new EWWW( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturn( false );

		Functions\when( 'get_home_path' );
		Functions\when( 'extract_from_markers' );
		Functions\expect( 'ewww_image_optimizer_webp_rewrite_verify' )
			->once()
			->andReturn( 'random rewrite rules' );

		Filters\expectApplied( 'rocket_webp_ewww_use_rewrite_rules' )
			->once()
			->with( false );

		$this->assertFalse( $subscriber->is_serving_webp() );
	}
}
