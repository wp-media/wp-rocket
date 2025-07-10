<?php
namespace WP_Rocket\Engine\Common\Head;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Head subscriber class.
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Head elements array.
	 *
	 * @var array
	 */
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
			'rocket_buffer' => [ 'insert_rocket_head', 100000 ],
			'rocket_head'   => 'print_head_elements',
		];
	}

	/**
	 * Print all head elements.
	 *
	 * @param string $content Head elements HTML.
	 * @return string
	 */
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
		$items = wpm_apply_filters_typed( 'array', 'rocket_head_items', [] );
		if ( empty( $items ) ) {
			return $content;
		}

		$this->head_items = [];
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

	/**
	 * Check if the item is duplicate.
	 *
	 * @param array $item Item to check.
	 * @return bool
	 */
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

	/**
	 * Prepare element HTML from the item array.
	 *
	 * @param array $element Item element.
	 * @return string
	 */
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

		ksort( $element, SORT_NATURAL );

		foreach ( $element as $key => $value ) {
			if ( is_int( $key ) ) {
				$attributes[] = $value;
				continue;
			}
			$attributes[] = $key . '="' . $this->esc_attribute( $key, $value ) . '"';
		}

		$attributes_html = ! empty( $attributes ) ? ' ' . implode( ' ', $attributes ) : '';

		return $open_tag . $attributes_html . '>' . $inner_content . $close_tag;
	}

	/**
	 * Insert rocket_head into the buffer HTML
	 *
	 * @param string $html Buffer HTML.
	 * @return string
	 */
	public function insert_rocket_head( $html ) {
		if ( empty( $html ) ) {
			return $html;
		}

		$filtered_buffer = preg_replace(
			'#</title>#iU',
			'</title>' . wpm_apply_filters_typed( 'string', 'rocket_head', '' ),
			$html,
			1
		);

		if ( empty( $filtered_buffer ) ) {
			return $html;
		}

		return $filtered_buffer;
	}

	/**
	 * Escape attribute value before printing it.
	 *
	 * @param string $attribute_name Attribute name.
	 * @param string $attribute_value Attribute value.
	 * @return string
	 */
	private function esc_attribute( $attribute_name, $attribute_value ) {
		if ( 'data-wpr-hosted-gf-parameters' === $attribute_name ) {
			return $attribute_value;
		}

		return esc_attr( $attribute_value );
	}
}
