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
			$variable_placeholder = ':root{' . $placeholder . ': ' . $url . ';}';
			$formatted_urls[]     = [
				'selector' => $selector,
				'style'    => $variable_placeholder,
			];
		}

		return $formatted_urls;
	}

	/**
	 * Remove pseudo classes.
	 *
	 * @param string $selector Selector to clean.
	 *
	 * @return string
	 */
	protected function remove_pseudo_classes( string $selector ): string {
		$result = preg_replace( '/::[\w\-]+/', '', $selector );

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
				if ( strlen( $first ) == strlen( $second ) ) {
					return 0;
				}
				return ( strlen( $first ) > strlen( $second ) ) ? -1 : 1;
			}
		);

		$selectors = explode( ',', $selector );

		$selectors = array_map(
			static function( $selector ) use ( $pseudo_elements_to_remove ) {

				$selector = preg_replace( '/::[\w-]+/', '', $selector );

				foreach ( $pseudo_elements_to_remove as $element ) {
					$selector = str_replace( $element, '', $selector );
				}
				if ( in_array( substr( $selector, -1 ), [ '&', '~', '+', '>' ] ) ) {
					$selector .= '*';
				}

				if ( ! $selector ) {
					return 'body';
				}

				return $selector;
			},
			$selectors
			);

		$selectors = array_unique( $selectors );

		$selector = implode( ',', $selectors );

		if ( ! $selector ) {
			return 'body';
		}

		return (string) $selector;
	}
}
