<?php

namespace WP_Rocket\Engine\DOM;

class Element {

	/**
	 * Defines the <meta> charset element.
	 */
	const META_CHARSET = '<meta http-equiv="content-type" content="text/html; charset=utf-8">';

	/**
	 * Searches in the HTML for the tag.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html HTML to search.
	 * @param string $tag  The HTML tag to find.
	 *
	 * @return string[]|bool An array of element(s) if exists; else false.
	 */
	public static function find( $html, $tag ) {
		$pattern = sprintf(
			'/<%1$s[^>]*?>[^<]*(?><\/%1$s>)?/i',
			preg_quote( $tag, '/' )
		);

		return self::get_element( $html, $pattern );
	}

	/**
	 * Searches for the tag with the given attribute.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html      HTML to search.
	 * @param string $tag       The HTML tag to find.
	 * @param string $attribute The attribute to find.
	 *
	 * @return string[]|bool An array of element(s) if exists; else false.
	 */
	public static function find_by_attribute( $html, $tag, $attribute ) {
		$pattern = sprintf(
			'/<%1$s [^>]*?\s*%2$s\s*="[^>]*?>[^<]*(?><\/%1$s>)?/i',
			preg_quote( $tag, '/' ),
			preg_quote( $attribute, '/' )
		);

		return self::get_element( $html, $pattern );
	}

	/**
	 * Gets the element(s) from the given HTML, if it exists.
	 *
	 * @since 3.6.2.1
	 *
	 * @param string $html    HTML to search.
	 * @param string $pattern The search regex pattern.
	 *
	 * @return string[]|bool An array of element(s) if exists; else false.
	 */
	private static function get_element( $html, $pattern ) {
		$matches = [];

		if ( preg_match( $pattern, $html, $matches ) ) {
			return $matches;
		}

		return false;
	}
}
