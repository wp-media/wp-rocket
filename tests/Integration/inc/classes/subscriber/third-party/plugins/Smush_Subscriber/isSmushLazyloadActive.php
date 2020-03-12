<?php
namespace WP_Rocket\Tests\Integration\inc\classes\third_party\plugins\Smush_Subscriber;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber::is_smush_lazyload_active
 * @group ThirdParty
 * @group Smush
 * @group WithSmush
 */
class Test_IsSmushLazyloadActive extends SmushSubscriberTestCase {

	public function testShouldNotDisableWPRocketLazyLoad() {
		// Disabled.
		$this->setSmushSettings(
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
		$this->setSmushSettings(
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
		$this->setSmushSettings(
			true,
			[]
		);

		$this->assertNotContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );
	}

	public function testShouldDisableWPRocketLazyLoadWhenAtLeastOneImageFormat() {
		$this->setSmushSettings(
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

		$this->setSmushSettings(
			true,
			[
				'jpeg' => false,
				'png'  => true,
				'gif'  => true,
				'svg'  => false,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->setSmushSettings(
			true,
			[
				'jpeg' => true,
				'png'  => true,
				'gif'  => true,
				'svg'  => false,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->setSmushSettings(
			true,
			[
				'jpeg' => true,
				'png'  => true,
				'gif'  => true,
				'svg'  => true,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );

		$this->setSmushSettings(
			true,
			[
				'png' => true,
			]
		);

		$this->assertContains( 'Smush', $this->subscriber->is_smush_lazyload_active( [] ) );
	}
}
