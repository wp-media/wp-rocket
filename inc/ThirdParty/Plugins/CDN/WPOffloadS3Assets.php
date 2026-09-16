<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\PluginCompatibilityInterface;

/**
 * Compatibility with the WP Offload S3 Assets addon.
 */
class WPOffloadS3Assets implements Subscriber_Interface, PluginCompatibilityInterface {
	/**
	 * Whether the WP Offload S3 Assets addon is active.
	 *
	 * @return bool
	 */
	public static function is_activated(): bool {
		return function_exists( 'as3cf_assets_init' );
	}

	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * The original compatibility only registered the aws_init hook in the admin
	 * context, so the admin guard is preserved here. update_option_as3cf_assets is
	 * a WP Rocket-facing option-update reaction, not a plugin lifecycle hook.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		if ( ! is_admin() ) {
			return [];
		}

		return [
			'aws_init'                   => [ 'as3cf_assets_compatibility', 13 ],
			'update_option_as3cf_assets' => [ 'maybe_deactivate_cdn', 10, 2 ],
		];
	}

	/**
	 * Disables the WP Rocket CDN option when the assets addon serves from S3.
	 *
	 * @return void
	 */
	public function as3cf_assets_compatibility() {
		global $as3cf_assets;

		if ( isset( $as3cf_assets ) && $as3cf_assets->is_plugin_setup() && 1 === (int) $as3cf_assets->get_setting( 'enable-addon' ) ) {
			// Disable WP Rocket CDN option.
			add_filter( 'rocket_readonly_cdn_option', '__return_true' );
		}
	}

	/**
	 * Deactivates WP Rocket CDN when the assets addon copy & serve is turned on.
	 *
	 * @param array $old_value Previous assets option value.
	 * @param array $new_value New assets option value.
	 * @return void
	 */
	public function maybe_deactivate_cdn( $old_value, $new_value ) {
		if ( $old_value['enable-addon'] !== $new_value['enable-addon'] && 1 === (int) $new_value['enable-addon'] ) {
			update_rocket_option( 'cdn', 0 );
		}
	}
}
