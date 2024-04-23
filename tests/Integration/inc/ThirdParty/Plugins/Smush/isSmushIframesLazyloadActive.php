<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Smush;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Smush::is_smush_iframes_lazyload_active
 * @group ThirdParty
 * @group Smush
 * @group WithSmush
 * @requires PHP >= 7.4
 */
class Test_IsSmushIframesLazyloadActive extends SmushSubscriberTestCase {
	/**
	 * @dataProvider addDataProviderThatShouldNotDisableWPRocketLazyLoad
	 */
	public function testShouldNotDisableWPRocketLazyLoad( $lazyload_enabled, array $lazyload_formats ) {
		$this->setSmushSettings( $lazyload_enabled, $lazyload_formats );

		$this->assertNotContains( 'Smush', $this->subscriber->is_smush_iframes_lazyload_active( [] ) );
	}

	/**
	 * @dataProvider addDataProviderThatShouldDisableWPRocketLazyLoad
	 */
	public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat( $lazyload_enabled, array $lazyload_formats ) {
		$this->setSmushSettings( $lazyload_enabled, $lazyload_formats );

		$this->assertContains( 'Smush', $this->subscriber->is_smush_iframes_lazyload_active( [] ) );
	}

	public function addDataProviderThatShouldNotDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'isSmushIframesLazyloadActiveNotDisable' );
	}

	public function addDataProviderThatShouldDisableWPRocketLazyLoad() {
		return $this->getTestData( __DIR__, 'isSmushIframesLazyloadActiveDisable' );
	}
}
