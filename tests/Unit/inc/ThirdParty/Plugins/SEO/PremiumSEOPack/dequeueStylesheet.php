<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\SEO\PremiumSEOPack;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\SEO\PremiumSEOPack;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SEO\PremiumSEOPack::dequeue_stylesheet
 *
 * @group PremiumSEOPack
 * @group ThirdParty
 */
class Test_DequeueStylesheet extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected outcome.
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_current_screen' )->justReturn( (object) [ 'id' => $config['screen_id'] ] );

		if ( $expected['dequeued'] ) {
			Functions\expect( 'wp_dequeue_style' )->once()->with( 'psp-main-style' );
		} else {
			Functions\expect( 'wp_dequeue_style' )->never();
		}

		( new PremiumSEOPack() )->dequeue_stylesheet();
	}
}
