<?php
namespace WP_Rocket\Tests\Unit\inc\classes\third_party\plugins\Smush_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber::is_smush_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Test_IsSmushLazyloadActive extends SmushSubscriberTestCase {

	public function testShouldNotDisableWPRocketLazyLoad() {
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

		$this->assertNotContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		// No image formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => false,
				'png'    => false,
				'gif'    => false,
				'svg'    => false,
				'foo'    => true,
				'iframe' => true,
			]
		);

		$this->assertNotContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		// Empty formats.
		$this->mock_is_smush_lazyload_enabled(
			true,
			[]
		);

		$this->assertNotContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );
	}

	public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat() {
		$this->mockCommonWpFunctions();

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg'   => true,
				'png'    => false,
				'gif'    => false,
				'svg'    => false,
				'foo'    => false,
				'iframe' => false,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg' => false,
				'png'  => true,
				'gif'  => true,
				'svg'  => false,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg' => true,
				'png'  => true,
				'gif'  => true,
				'svg'  => false,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'jpeg' => true,
				'png'  => true,
				'gif'  => true,
				'svg'  => true,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->mock_is_smush_lazyload_enabled(
			true,
			[
				'png' => true,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );
	}
}
