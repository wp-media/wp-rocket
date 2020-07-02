<?php


namespace WP_Rocket\Engine\Optimization;


trait CSSTrait {

	/**
	 * Applies font-display:swap to all font-family rules without a previously set font-display property.
	 *
	 * @since 3.7
	 *
	 * @param string $css_file_content CSS file content to modify.
	 *
	 * @return string Modified CSS content.
	 */
	private function apply_font_display_swap( $css_file_content ) {
		$css_file_content = (string) $css_file_content;

		return preg_replace_callback(
			'/(?:@font-face)\s*{\s*((?:.|\s)*)\s*}/',
			function ( $matches ) {
				if ( false !== strpos( $matches[1], 'font-display' ) ) {
					return $matches;
				}

				$matches[1] = 'font-display:swap;' . $matches[1];

				return $matches;
			},
			$css_file_content
		);
	}
}
