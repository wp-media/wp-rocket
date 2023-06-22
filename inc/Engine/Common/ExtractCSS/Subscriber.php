<?php
namespace WP_Rocket\Engine\Common\ExtractCSS;

use WP_Rocket\EventManagement\SubscriberInterface;

class Subscriber implements SubscriberInterface {

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
	 * Extract CSS files from the HTML.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function extract_css_files_from_html( array $data ): array {
		return $data;
	}

	/**
	 * Extract inline CSS from the HTML.
	 *
	 * @param array $data Data sent.
	 * @return array
	 */
	public function extract_inline_css_from_html( array $data ): array {
		return $data;
	}
}
