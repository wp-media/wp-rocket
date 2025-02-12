<?php
namespace WP_Rocket\Engine\Common\Head;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	private $head_items = [];

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
		];
	}

	public function print_head_elements( $content ) {
        /**
         * Filter Head elements array.
         *
         * @param array $head_items Elements to be added to head after closing of title tag.
         *
         * Priority 10: preconnect
         * Priority 30: preload
         * Priority 50: styles
         * @returns array
         */
		$items = wpm_apply_filters_typed( 'array','rocket_head_items', [] );
		if ( empty( $items ) ) {
			return $content;
		}

		// Combine elements.
		$elements = '';
		foreach ( $items as $item ) {
			// Make sure that we don't have duplication based on `href` inside each `rel`.
			if ( $this->is_duplicate( $item ) ) {
				continue;
			}
			$elements .= "\n" . $this->prepare_element( $item );
		}

		return $content . $elements;
	}

	private function is_duplicate( $item ) {
		if ( empty( $item['rel'] ) || empty( $item['href'] ) ) {
			return false;
		}

		if ( ! isset( $this->head_items[ $item['rel'] ] ) ) {
			$this->head_items[ $item['rel'] ] = [];
		}

		if ( ! isset( $this->head_items[ $item['rel'] ][ $item['href'] ] ) ) {
			$this->head_items[ $item['rel'] ][ $item['href'] ] = true;
			return false;
		}

		return true;
	}

	private function prepare_element( $element ) {
		$open_tag = '';
		if ( ! empty( $element['open_tag'] ) ) {
			$open_tag = $element['open_tag'];
			unset( $element['open_tag'] );
		}

		$close_tag = '';
		if ( ! empty( $element['close_tag'] ) ) {
			$close_tag = $element['close_tag'];
			unset( $element['close_tag'] );
		}

		$inner_content = '';
		if ( ! empty( $element['inner_content'] ) ) {
			$inner_content = $element['inner_content'];
			unset( $element['inner_content'] );
		}

		$attributes = [];

		foreach ( $element as $key => $value ) {
			if (is_int( $key ) ) {
				$attributes[] = $value;
				continue;
			}
			$attributes[] = $key . '="' . esc_attr( $value ) . '"';
		}

		$attributes_html = ! empty( $attributes ) ? implode( ' ', $attributes ) : '';

		return $open_tag . ' ' . $attributes_html . '>' . $inner_content . $close_tag;
	}
}
