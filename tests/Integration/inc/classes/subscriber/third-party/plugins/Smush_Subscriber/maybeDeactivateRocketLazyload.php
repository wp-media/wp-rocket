<?php
namespace WP_Rocket\Tests\Integration\inc\classes\third_party\plugins\Smush_Subscriber;

use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber::maybe_deactivate_rocket_lazyload
 * @group ThirdParty
 * @group Smush
 * @group WithSmush
 */
class Test_MaybeDeactivateRocketLazyload extends SmushSubscriberTestCase {
	private $option_hook_prefix = 'pre_get_rocket_option_';
	private $rocket_settings;
	private $filters;

	public function setUp() {
		global $wp_filter;

		parent::setUp();

		$this->filters = [
			$this->option_hook_prefix . 'lazyload'         => null,
			$this->option_hook_prefix . 'lazyload_iframes' => null,
			'update_option_wp_rocket_settings'             => null,
		];

		foreach ( $this->filters as $tag => $list ) {
			if ( ! empty( $wp_filter[ $tag ] ) ) {
				$this->filters[ $tag ] = $wp_filter[ $tag ];
				unset( $wp_filter[ $tag ] );
			}
		}

		$this->rocket_settings = get_option( 'wp_rocket_settings', [] );
	}

	public function tearDown() {
		parent::tearDown();

		foreach ( $this->filters as $tag => $list ) {
			if ( ! empty( $this->filters[ $tag ] ) ) {
				$wp_filter[ $tag ]     = $this->filters[ $tag ];
				$this->filters[ $tag ] = null;
			}
		}

		update_option( 'wp_rocket_settings', $this->rocket_settings );

		$this->rocket_settings = null;
	}

	public function testShouldNotDisableWPRocketLazyLoad() {
		Functions\expect( 'update_rocket_option' )->never();

		// Smush not enabled, WPR enabled.
		$this->setSmushSettings(
			false,
			[]
		);

		$this->setRocketSettings(
			[
				'lazyload'         => 1,
				'lazyload_iframes' => 1,
			 ]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		$new_settings = $this->getLazyloadSettings();

		$this->assertNotEmpty( $new_settings['lazyload'] );
		$this->assertNotEmpty( $new_settings['lazyload_iframes'] );

		// Smush enabled, WPR not enabled.
		$this->setSmushSettings(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		$this->setRocketSettings(
			[
				'lazyload'         => 0,
				'lazyload_iframes' => 0,
			 ]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		$new_settings = $this->getLazyloadSettings();

		$this->assertEmpty( $new_settings['lazyload'] );
		$this->assertEmpty( $new_settings['lazyload_iframes'] );
	}

	public function testShouldDisableWPRocketLazyLoadForImagesWhenSmushLazyLoadForImagesIsEnabled() {
		$this->setSmushSettings(
			true,
			[
				'jpeg'   => true,
				'iframe' => false,
			]
		);

		$this->setRocketSettings(
			[
				'lazyload'         => 1,
				'lazyload_iframes' => 1,
			 ]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		$new_settings = $this->getLazyloadSettings();

		$this->assertEmpty( $new_settings['lazyload'] );
		$this->assertNotEmpty( $new_settings['lazyload_iframes'] );
	}

	public function testShouldDisableWPRocketLazyLoadForIframesWhenSmushLazyLoadForIframesIsEnabled() {
		$this->setSmushSettings(
			true,
			[
				'jpeg'   => false,
				'iframe' => true,
			]
		);

		$this->setRocketSettings(
			[
				'lazyload'         => 1,
				'lazyload_iframes' => 1,
			 ]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		$new_settings = $this->getLazyloadSettings();

		$this->assertNotEmpty( $new_settings['lazyload'] );
		$this->assertEmpty( $new_settings['lazyload_iframes'] );
	}

	public function testShouldDisableWPRocketBothLazyLoadWhenSmushLazyLoadForImagesAndIframesIsEnabled() {
		$this->setSmushSettings(
			true,
			[
				'jpeg'   => true,
				'iframe' => true,
			]
		);

		$this->setRocketSettings(
			[
				'lazyload'         => 1,
				'lazyload_iframes' => 1,
			 ]
		);

		$this->subscriber->maybe_deactivate_rocket_lazyload();

		$new_settings = $this->getLazyloadSettings();

		$this->assertEmpty( $new_settings['lazyload'] );
		$this->assertEmpty( $new_settings['lazyload_iframes'] );
	}

	private function setRocketSettings( array $settings ) {
		$current_settings = (array) get_option( 'wp_rocket_settings', [] );
		$current_settings = array_merge( $current_settings, $settings );
		update_option( 'wp_rocket_settings', $current_settings );
	}

	private function getLazyloadSettings() {
		$settings = (array) get_option( 'wp_rocket_settings', [] );

		return [
			'lazyload'         => isset( $settings['lazyload'] ) ? $settings['lazyload'] : null,
			'lazyload_iframes' => isset( $settings['lazyload_iframes'] ) ? $settings['lazyload_iframes'] : null,
		];
	}
}
