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
}
