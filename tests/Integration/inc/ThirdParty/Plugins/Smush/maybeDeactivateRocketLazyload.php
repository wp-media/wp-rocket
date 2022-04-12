<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Smush;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Smush::maybe_deactivate_rocket_lazyload
 * @group ThirdParty
 * @group Smush
 * @group WithSmush
 */
class Test_MaybeDeactivateRocketLazyload extends SmushSubscriberTestCase {
	private $option_hook_prefix = 'pre_get_rocket_option_';
	private $rocket_settings;
	private $filters;

	public function set_up() {
		global $wp_filter;

		parent::set_up();

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

	public function tear_down() {
		parent::tear_down();

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

		$new_settings = $this->getRocketLazyloadSettings();

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

		$new_settings = $this->getRocketLazyloadSettings();

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

		$new_settings = $this->getRocketLazyloadSettings();

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

		$new_settings = $this->getRocketLazyloadSettings();

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

		$new_settings = $this->getRocketLazyloadSettings();

		$this->assertEmpty( $new_settings['lazyload'] );
		$this->assertEmpty( $new_settings['lazyload_iframes'] );
	}

	private function setRocketSettings( array $settings ) {
		foreach ( $settings as $setting => $value ) {
			$this->options_data->set( $setting, $value );
		}

		$this->options_api->set( 'settings', $this->options_data->get_options() );
		$this->set_reflective_property( $this->options_data, 'options', $this->subscriber );
	}

	private function getRocketLazyloadSettings() {
		$settings = (array) get_option( 'wp_rocket_settings', [] );

		return [
			'lazyload'         => isset( $settings['lazyload'] ) ? $settings['lazyload'] : null,
			'lazyload_iframes' => isset( $settings['lazyload_iframes'] ) ? $settings['lazyload_iframes'] : null,
		];
	}
}
