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
}
