<?php
namespace WP_Rocket\Engine\Common\Head;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

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
		return [
			'rocket_head' => 'print_head_elements',
			'rocket_head_items' => [
				[ 'preload_resources', 10 ],
				[ 'resource_hints', 11 ],
			],
		];
	}

	public function print_head_elements( $content ) {
		$items = wpm_apply_filters_typed( 'array','rocket_head_items', [] );
		if ( empty( $items ) ) {
			return $content;
		}

		// Combine elements.
		$elements = '';
		foreach ( $items as $item ) {
			// Make sure that we don't have duplication based on `href` inside each `rel`.
			foreach ( $item as $key => $value ) {
				if ( in_array( $key, [ 'open_tag', 'close_tag', 'inner_content' ], true ) ) {
					$elements .= $value;
					continue;
				}
				$elements .= '"' . $key . '"="' . esc_attr( $value ) . '" ';
			}

			$elements .= "\n";
		}

		return $content . $elements;
	}

	public function preload_resources( $items ) {
		return $items;
	}

	public function resource_hints( $items ) {
		return $items;
	}
}
