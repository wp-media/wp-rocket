<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Smush;

use WP_Rocket\ThirdParty\Plugins\Smush;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Smush::is_smush_iframes_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Test_IsSmushIframesLazyloadActive extends SmushSubscriberTestCase {

	public function testShouldNotDisableWPRocketLazyLoad() {
		$subscriber = new Smush( $this->createMock( 'WP_Rocket\Admin\Options' ), $this->createMock( 'WP_Rocket\Admin\Options_Data' ) );

		// Disabled.
		$this->mock_is_smush_lazyload_enabled(
			false,
			[
				'jpeg'   => true,
				'png'    => true,
				'gif'    => true,
				'svg'    => true,
				'iframe' => true,
			]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );

		// No image formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'png'    => true,
				'gif'    => true,
				'svg'    => true,
				'foo'    => true,
				'iframe' => false,
			]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );

		// Empty formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[]
		);

		$this->assertEmpty( $subscriber->is_smush_iframes_lazyload_active( [] ) );
	}

	public function testShouldDisableWPRocketLazyLoadWhenIframeFormat() {
		$this->mockCommonWpFunctions();

		$subscriber = new Smush( $this->createMock( 'WP_Rocket\Admin\Options' ), $this->createMock( 'WP_Rocket\Admin\Options_Data' ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => false,
				'png'    => false,
				'gif'    => false,
				'svg'    => false,
				'foo'    => false,
				'iframe' => true,
			]
		);

		$this->assertContains( 'Smush', $subscriber->is_smush_iframes_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'iframe' => true,
			]
		);

		$this->assertContains( 'Smush', $subscriber->is_smush_iframes_lazyload_active( [] ) );
	}
}
