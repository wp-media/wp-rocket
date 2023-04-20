<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\WebP;

use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;
use WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp\Webp_Interface;

abstract class AbstractWebp {
	/**
	 * CDNSubscriber instance.
	 *
	 * @var CDNSubscriber
	 */
	protected $cdn_subscriber;

	/**
	 * Constructor.
	 *
	 * @since 3.12.6
	 *
	 * @param CDNSubscriber $cdn_subscriber CDNSubscriber instance.
	 */
	public function __construct( CDNSubscriber $cdn_subscriber ) {
		$this->cdn_subscriber = $cdn_subscriber;
	}

	/**
	 * Get a list of active plugins that convert and/or serve webp images.
	 *
	 * @since 3.12.6
	 *
	 * @return array An array of Webp_Interface objects.
	 */
	protected function get_webp_plugins() {
		/**
		 * Add Webp plugins.
		 *
		 * @since 3.4
		 *
		 * @param array $webp_plugins An array of Webp_Interface objects.
		 */
		$webp_plugins = (array) apply_filters( 'rocket_webp_plugins', [] );

		if ( ! $webp_plugins ) {
			// Somebody probably messed up.
			return [];
		}

		foreach ( $webp_plugins as $plugin_key => $plugin ) {
			if ( ! $plugin instanceof Webp_Interface ) {
				unset( $webp_plugins[ $plugin_key ] );
				continue;
			}

			if ( ! $this->is_plugin_active( $plugin->get_basename() ) ) {
				unset( $webp_plugins[ $plugin_key ] );
			}
		}

		return $webp_plugins;
	}

	/**
	 * Tell if a plugin is active.
	 *
	 * @since 3.12.6
	 *
	 * @param  string $plugin_basename A plugin basename.
	 * @return bool
	 */
	protected function is_plugin_active( string $plugin_basename ): bool {
		if ( doing_action( 'deactivate_' . $plugin_basename ) ) {
			return false;
		}

		if ( doing_action( 'activate_' . $plugin_basename ) ) {
			return true;
		}

		return rocket_is_plugin_active( $plugin_basename );
	}

	/**
	 * Tell if WP Rocket uses a CDN for images.
	 *
	 * @since 3.12.6
	 *
	 * @return bool
	 */
	protected function is_using_cdn(): bool {
		// Don't use `$this->options_data->get( 'cdn' )` here, we need an up-to-date value when the CDN option changes.
		$use = get_rocket_option( 'cdn', 0 ) && $this->cdn_subscriber->get_cdn_hosts( [], [ 'all', 'images' ] );
		/**
		 * Filter whether WP Rocket is using a CDN for webp images.
		 *
		 * @since 3.4
		 *
		 * @param bool $use True if WP Rocket is using a CDN for webp images. False otherwise.
		 */
		return (bool) apply_filters( 'rocket_webp_is_using_cdn', $use );
	}
}
