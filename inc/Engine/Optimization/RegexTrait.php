<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization;

trait RegexTrait {

	/**
	 * Finds nodes matching the pattern in the HTML.
	 *
	 * @param string $pattern Pattern to match.
	 * @param string $html HTML content.
	 * @param string $modifiers Regex modifiers.
	 *
	 * @return array
	 */
	protected function find( string $pattern, string $html, string $modifiers = 'Umsi' ) {
		preg_match_all( '/' . $pattern . '/' . $modifiers, $html, $matches, PREG_SET_ORDER );

		if ( empty( $matches ) ) {
			return [];
		}

		return $matches;
	}

	/**
	 * Hides unwanted blocks from the HTML to be parsed for optimization
	 *
	 * @since 3.1.4
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	protected function hide_comments( $html ) {
		$html = preg_replace( '#<!--\s*noptimize\s*-->.*?<!--\s*/\s*noptimize\s*-->#is', '', $html );
		$html = preg_replace( '/<!--(.*)-->/Uis', '', $html );

		return $html;
	}
}
