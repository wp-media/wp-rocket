<?php

namespace WP_Rocket\ThirdParty\Plugins;

use Smush\Core\Settings;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with RevolutionSlider.
 *
 * @since  3.9.2
 */
class RevolutionSlider implements Subscriber_Interface {

	/**
	 * Subscribed events for RevolutionSlider.
	 *
	 * @since  3.9.2
	 *
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'RS_REVISION' ) || version_compare( RS_REVISION, '6.5.5', '<' ) ) {
			return [];
		}

		return [
			'rocket_exclude_defer_js' => 'exclude_defer_js',
		];
	}

	/**
	 * Excludes jquery and jquery migrate JS files from defer JS
	 *
	 * @since 3.9.2
	 *
	 * @param array $exclude_defer_js Array of JS file paths to be excluded.
	 * @return array
	 */
	public function exclude_defer_js( $exclude_defer_js ) {
		$exclude_defer_js[] = '/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js';
		$exclude_defer_js[] = '/jquery-migrate(.min)?.js';

		return $exclude_defer_js;
	}
}
