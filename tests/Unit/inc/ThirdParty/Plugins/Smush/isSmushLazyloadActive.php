<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use WP_Rocket\ThirdParty\Plugins\Smush;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Smush::is_smush_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Test_IsSmushLazyloadActive extends SmushSubscriberTestCase {
	/**
	 * @dataProvider addDataProviderThatShouldNotDisableWPRocketLazyLoad
	 */
	public function testShouldNotDisableWPRocketLazyLoad( $lazyload_enabled, array $lazyload_formats ) {
		$subscriber = new Smush( $this->createMock( 'WP_Rocket\Admin\Options' ), $this->createMock( 'WP_Rocket\Admin\Options_Data' ) );

		$this->mock_is_smush_lazyload_enabled( $lazyload_enabled, $lazyload_formats );

		$this->assertNotContains( 'Smush', $subscriber->is_smush_lazyload_active( [] ) );
	}

	/**
	 * @dataProvider addDataProviderThatShouldDisableWPRocketLazyLoad
	 */
	public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat( $lazyload_enabled, array $lazyload_formats ) {
		$this->mockCommonWpFunctions();

		$subscriber = new Smush( $this->createMock( 'WP_Rocket\Admin\Options' ), $this->createMock( 'WP_Rocket\Admin\Options_Data' ) );

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
