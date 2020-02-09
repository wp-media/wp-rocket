<?php
namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Images\Webp\EwwwSubscriber;

use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\EWWW_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;

/**
 * @group ThirdParty
 */
class TestIsServingWebp extends TestCase {
	/**
	 * Test EWWW_Subscriber->is_serving_webp() should return true when ExactDN is enabled.
	 */
	public function testShouldReturnTrueWhenExactdnIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->once()
			->andReturn( true );

		$this->assertTrue( $subscriber->is_serving_webp() );
	}

	/**
	 * Test EWWW_Subscriber->is_serving_webp() should return true when JS rewrite is enabled.
	 */
	public function testShouldReturnTrueWhenJsRewriteIsEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

		Functions\expect( 'ewww_image_optimizer_get_option' )
			->twice()
			->andReturnUsing( function( $option_name ) {
				return 'ewww_image_optimizer_webp_for_cdn' === $option_name;
			} );

		$this->assertTrue( $subscriber->is_serving_webp() );
	}

	/**
	 * Test EWWW_Subscriber->is_serving_webp() should return true when .htaccess rewrite rules are enabled.
	 */
	public function testShouldReturnTrueWhenHtaccessRewriteRewriteRulesAreEnabled() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

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

	/**
	 * Test EWWW_Subscriber->is_serving_webp() should return false when no serving methods are enabled in EWWW.
	 */
	public function testShouldReturnFalseWhenNothingEnabledInEwww() {
		$optionsData = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$subscriber  = new EWWW_Subscriber( $optionsData );

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
