<?php
namespace WP_Rocket\Engine\Media\Lazyload\CSS\Admin;

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
	 * Add the field to the metaboxes.
	 *
	 * @param array $fields Metaboxes fields.
	 * @return array
	 */
	public function add_meta_box( array $fields ) {
		return $fields;
	}

	/**
	 * Maybe display the error notice.
	 *
	 * @return void
	 */
	public function maybe_add_error_notice() {

	}

}
