<?php
namespace WP_Rocket\Engine\Media\Lazyload\CSS;

use WP_Post;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Cache instance.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 *
	 * Instantiate class.
	 *
	 * @param CacheInterface $cache Cache instance.
	 */
	public function __construct( CacheInterface $cache ) {
		$this->cache = $cache;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * The array key is the event name. The value can be:
	 *
	 *  * The method name
	 *  * An array with the method name and priority
	 *  * An array with the method name, priority and number of accepted arguments
	 *
	 * For instance:
	 *
	 *  * array('hook_name' => 'method_name')
	 *  * array('hook_name' => array('method_name', $priority))
	 *  * array('hook_name' => array('method_name', $priority, $accepted_args))
	 *  * array('hook_name' => array(array('method_name_1', $priority_1, $accepted_args_1)), array('method_name_2', $priority_2, $accepted_args_2)))
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [];
	}

	/**
	 * Replace CSS images by the lazyloaded version.
	 *
	 * @param string $html page HTML.
	 * @return string
	 */
	public function maybe_replace_css_images( string $html ): string {
		return $html;
	}

	/**
	 * Clear the lazyload CSS files.
	 *
	 * @return void
	 */
	public function clear_generated_css() {

	}

	/**
	 * Clear the lazyload CSS linked with a post.
	 *
	 * @param WP_Post $post post cleared.
	 * @return void
	 */
	public function clear_generate_css_post( WP_Post $post ) {

	}

	/**
	 * Insert the lazyload script.
	 *
	 * @return void
	 */
	public function insert_lazyload_script() {

	}

	/**
	 * Create the lazyload file for CSS files.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function create_lazy_css_files( array $data ): array {
		return $data;
	}

	/**
	 * Create the lazyload file for inline CSS.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function create_lazy_inline_css( array $data ): array {
		return $data;
	}
}
