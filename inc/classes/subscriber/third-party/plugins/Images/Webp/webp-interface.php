<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Images\Webp;

/**
 * Interface to use for webp subscribers.
 *
 * @since 3.4
 */
interface Webp_Interface {

	/**
	 * Get the plugin name.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the plugin identifier.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Tell if the plugin converts images to webp.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_converting_to_webp();

	/**
	 * Tell if the plugin serves webp images on frontend.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_serving_webp();

	/**
	 * Tell if the plugin uses a CDN-compatible technique to serve webp images on frontend.
	 *
	 * @since 3.4
	 *
	 * @return bool
	 */
	public function is_serving_webp_compatible_with_cdn();

	/**
	 * Get the plugin basename.
	 *
	 * @since 3.4
	 *
	 * @return string
	 */
	public function get_basename();
}
