<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Slider;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with Meta Slider.
 */
class MetaSlider implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether Meta Slider is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return class_exists( 'MetaSliderPlugin' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'metaslider_nivo_slider_image_attributes' => 'deactivate_lazyload',
		];
	}

	/**
	 * Prevents LazyLoad from being applied to Meta Slider (Nivo Slider) images.
	 *
	 * @param array $slide Slide attributes.
	 * @return array
	 */
	public function deactivate_lazyload( $slide ) {
		$slide['data-no-lazy'] = 1;

		return $slide;
	}
}
