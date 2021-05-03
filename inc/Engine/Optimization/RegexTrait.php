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

}
