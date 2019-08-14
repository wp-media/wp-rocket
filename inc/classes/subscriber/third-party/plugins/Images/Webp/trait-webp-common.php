<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

/**
 * Trait for webp subscribers, focussed on plugins that serve webp images on frontend.
 *
 * @since  3.4
 * @author Grégory Viguier
 */
trait Webp_Common {

	/**
	 * Register the plugin.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $webp_plugins An array of Webp_Interface objects.
	 * @return array
	 */
	public function register( $webp_plugins ) {
		$webp_plugins[] = $this;
		return $webp_plugins;
	}

	/**
	 * On plugin activation, deactivate Rocket webp cache if the plugin is serving webp.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_activation() {
		if ( $this->is_serving_webp() ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * On plugin deactivation, activate Rocket webp cache if the plugin is serving webp.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function plugin_deactivation() {
		if ( $this->is_serving_webp() ) {
			$this->trigger_webp_change();
		}
	}

	/**
	 * Trigger an action when the webp feature is enabled/disabled in a third party plugin.
	 *
	 * @since  3.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function trigger_webp_change() {
		/**
		 * Trigger an action when the webp feature is enabled/disabled in a third party plugin.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 */
		do_action( 'rocket_third_party_webp_change' );
	}

	/**
	 * Tell if WP Rocket uses a CDN for images.
	 *
	 * @since  3.4
	 * @access private
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_using_cdn() {
		// Don't use `$this->options->get( 'cdn' )` here, we need an up-to-date value when the CDN option changes.
		$use = get_rocket_option( 'cdn' ) && $this->cdn->get_cdn_urls( [ 'all', 'images' ] );
		/**
		 * Filter whether WP Rocket is using a CDN for webp images.
		 *
		 * @since  3.4
		 * @author Grégory Viguier
		 *
		 * @param bool   $use       True if WP Rocket is using a CDN for webp images. False otherwise.
		 * @param string $plugin_id The plugin identifier.
		 */
		return (bool) apply_filters( 'rocket_webp_is_using_cdn', $use, $this->get_id() );
	}
}
