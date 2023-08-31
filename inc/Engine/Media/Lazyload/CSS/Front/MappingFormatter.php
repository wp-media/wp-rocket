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

		$original_pseudo_elements = [
			':before',
			':after',
			':first-line',
			':first-letter',
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



		$result = preg_replace( '/::[\w\-]+/', '', $selector );

		foreach ( $pseudo_elements_to_remove as $element ) {
			$selector = str_replace( $element, '', $selector );
		}

		if ( ! $result ) {
			return 'body';
		}

		return (string) $result;
	}
}
