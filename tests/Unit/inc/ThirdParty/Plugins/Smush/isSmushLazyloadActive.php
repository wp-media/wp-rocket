<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Smush;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Smush::is_smush_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Test_IsSmushLazyloadActive extends SmushSubscriberTestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider addDataProviderThatShouldNotDisableWPRocketLazyLoad
	 */
	public function testShouldNotDisableWPRocketLazyLoad( $lazyload_enabled, array $lazyload_formats ) {
		$subscriber = new Smush( Mockery::mock( Options::class ), Mockery::mock( Options_Data::class ) );

		$this->mock_is_smush_lazyload_enabled( $lazyload_enabled, $lazyload_formats );

		$this->assertNotContains( 'Smush', $subscriber->is_smush_lazyload_active( [] ) );
	}

	/**
	 * @dataProvider addDataProviderThatShouldDisableWPRocketLazyLoad
	 */
	public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat( $lazyload_enabled, array $lazyload_formats ) {

		$subscriber = new Smush( Mockery::mock( Options::class ), Mockery::mock( Options_Data::class ) );

		$this->mock_is_smush_lazyload_enabled( $lazyload_enabled, $lazyload_formats );

		$this->assertContains( 'Smush', $subscriber->is_smush_lazyload_active( [] ) );
	}

	public function addDataProviderThatShouldNotDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'isSmushLazyloadActiveNotDisable' );
	}

	public function addDataProviderThatShouldDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'isSmushLazyloadActiveDisable' );
	}
}
