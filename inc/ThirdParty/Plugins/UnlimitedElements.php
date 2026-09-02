<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Subscriber for compatibility with Unlimited Elements.
 */
class UnlimitedElements implements Subscriber_Interface, PluginCompatibilityInterface {


	/**
	 * Whether the target third-party plugin is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return (bool) rocket_get_constant( 'UNLIMITED_ELEMENTS_INC' );
	}

	/**
	 * Subscriber for Unlimited Elements.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [ 'rocket_rucss_inline_content_exclusions' => 'exclude_inline_from_rucss' ];
	}

	/**
	 * Exclude inline style from RUCSS.
	 *
	 * @param array $excluded excluded css.
	 * @return array
	 */
	public function exclude_inline_from_rucss( $excluded ) {
		$excluded[] = '#uc_';

		return $excluded;
	}
}
