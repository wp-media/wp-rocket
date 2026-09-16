<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with WP Offload S3 / Offload Media.
 */
class WPOffloadS3 implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether WP Offload S3 is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return function_exists( 'as3cf_init' ) || function_exists( 'as3cf_pro_init' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * The original compatibility only registered in the admin context, so the
	 * admin guard is preserved here.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		if ( ! is_admin() ) {
			return [];
		}

		return [
			'aws_init' => [ 'as3cf_compatibility', 12 ],
		];
	}

	/**
	 * Removes the images option from the WP Rocket CDN dropdown when serving from S3.
	 *
	 * @return void
	 */
	public function as3cf_compatibility() {
		global $as3cf;

		if ( isset( $as3cf ) && $as3cf->is_plugin_setup() && 1 === (int) $as3cf->get_setting( 'serve-from-s3' ) ) {
			// Remove images option from WP Rocket CDN dropdown settings.
			add_filter( 'rocket_allow_cdn_images', '__return_false' );
		}
	}
}
