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
				'hash'     => $hash,
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
		if ( ! $result ) {
			return 'body';
		}
		return (string) $result;
	}
}
