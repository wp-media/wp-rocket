<?php

declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Ezoic implements Subscriber_Interface {
	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_plugins_to_deactivate'              => 'add_conflict',
			'rocket_plugins_to_deactivate_explanations' => 'add_conflict_explanations',
		];
	}

	/**
	 * Adds Ezoic plugin to plugins to deactivate array
	 *
	 * @param array $plugins List of recommended plugins to deactivate.
	 *
	 * @return array
	 */
	public function add_conflict( $plugins ) {
		$plugins['ezoic'] = 'ezoic-integration/ezoic-integration.php';

		return $plugins;
	}

	/**
	 * Adds explanation for deactivation recommendation
	 *
	 * @param array $plugins_explanations List of recommended plugins to deactivate explanations.
	 *
	 * @return array
	 */
	public function add_conflict_explanations( $plugins_explanations ) {
		$plugins_explanations['ezoic'] = sprintf(
			// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
			__( 'This plugin blocks WP Rocket\'s caching and optimizations. Deactivate it and use %1$sEzoic\'s nameserver integration%2$s instead.', 'rocket' ),
			'<a href="https://support.ezoic.com/support/solutions/articles/48000453926-name-server-integration" target="_blank" rel="noopener noreferrer">',
			'</a>'
		);

		return $plugins_explanations;
	}
}
