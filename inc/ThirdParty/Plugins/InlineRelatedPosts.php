<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for compatibility with Inline Related Posts.
 */
class InlineRelatedPosts implements Subscriber_Interface {


	/**
	 * Subscriber for Inline Related Posts.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'IRP_PLUGIN_SLUG' ) ) {
			return [];
		}

		$events['rocket_rucss_inline_content_exclusions'] = 'excluded_inline_from_rucss';

		return $events;
	}

	/**
	 * Exclude inline style from RUCSS.
	 *
	 * @param array $excluded excluded css.
	 * @return array
	 */
	public function excluded_inline_from_rucss( $excluded ) {
		$excluded[] = '.centered-text-area';
		$excluded[] = '.ctaText';

		return $excluded;
	}
}
