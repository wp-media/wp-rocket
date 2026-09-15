<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with Premium SEO Pack.
 *
 * @see http://premiumseopack.com
 */
class PremiumSEOPack implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether Premium SEO Pack is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return class_exists( 'psp' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_print_styles' => [ 'dequeue_stylesheet', 11 ],
		];
	}

	/**
	 * Dequeues the Premium SEO Pack stylesheet on the WP Rocket settings page.
	 *
	 * Dequeueing this stylesheet unfreezes the WP Rocket settings page.
	 *
	 * @return void
	 */
	public function dequeue_stylesheet() {
		// Return on all pages but the WP Rocket settings page.
		$screen = get_current_screen();

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		wp_dequeue_style( 'psp-main-style' );
	}
}
