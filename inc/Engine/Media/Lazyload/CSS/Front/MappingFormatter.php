<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Front;

class MappingFormatter {

	/**
	 * Format data for the Mapping file.
	 *
	 * @param array $data Data to format.
	 * @return array
	 */
	public function format( array $data ): array {
		$formatted_urls = [];

		foreach ( $data as $datum ) {
			$hash = $datum['hash'];

			$selector = $datum['selector'];
			$selector = $this->remove_pseudo_classes( $selector );
			$url      = $datum['url'];

			$placeholder          = "--wpr-bg-$hash";
			$img_url              = "url('$url')";
			$variable_placeholder = $datum['selector'] . '{' . $placeholder . ': ' . $img_url . ';}';
			$formatted_urls[]     = [
				'selector' => $selector,
				'style'    => $variable_placeholder,
				'hash'     => $hash,
				'url'      => $url,
			];
		}

		return $formatted_urls;
	}

	/**
	 * Get pseudo elements to remove.
	 *
	 * @return string[]
	 */
	private function get_pseudo_elements_to_remove() {
		$original_pseudo_elements = [
			':before',
			':after',
			':first-line',
			':first-letter',
			':active',
			':hover',
			':focus',
			':visited',
			':focus-within',
			':focus-visible',
		];

		/**
		 * Pseudo elements to remove from lazyload CSS selector.
		 *
		 * @param string[] Pseudo elements to remove.
		 */
		$pseudo_elements_to_remove = apply_filters( 'rocket_lazyload_css_ignored_pseudo_elements', $original_pseudo_elements );

		if ( ! is_array( $original_pseudo_elements ) ) {
			$pseudo_elements_to_remove = $original_pseudo_elements;
		}

		usort(
			$pseudo_elements_to_remove,
			static function ( $first, $second ) {
				if ( strlen( $first ) === strlen( $second ) ) {
					return 0;
				}
				return ( strlen( $first ) > strlen( $second ) ) ? -1 : 1;
			}
		);

		return $pseudo_elements_to_remove;
	}

	/**
	 * Remove pseudo classes from the selector while mapping on each selector.
	 *
	 * @param string $selector Selector to clean.
	 * @return string
	 */
	public function remove_pseudo_classes_for_selector( string $selector ): string {
		$selector = preg_replace( '/::[\w-]+/', '', $selector );

		$pseudo_elements_to_remove = $this->get_pseudo_elements_to_remove();
		foreach ( $pseudo_elements_to_remove as $element ) {
			$selector = str_replace( $element, '', $selector );
		}
		if ( in_array( substr( $selector, -1 ), [ '&', '~', '+', '>' ], true ) ) {
			$selector .= '*';
		}

		if ( ! $selector ) {
			return 'body';
		}

		return $selector;
	}


	/**
	 * Remove pseudo classes from the selector.
	 *
	 * @param string $selector Selector to clean.
	 * @return string
	 */
	protected function remove_pseudo_classes( string $selector ): string {
		$selectors = explode( ',', $selector );

		$selectors = array_map( [ $this, 'remove_pseudo_classes_for_selector' ], $selectors );

		$selectors = array_unique( $selectors );

		$selector = implode( ',', $selectors );

		if ( ! $selector ) {
			return 'body';
		}

		return (string) $selector;
	}
}
