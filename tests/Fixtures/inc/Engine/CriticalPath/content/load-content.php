<?php
namespace WP_Rocket\Tests\Fixture\CriticalPath\content;

/**
 * Returns the requested HTML content.
 *
 * @since 1.0.0
 *
 * @param string $filename HTML filename without the .html extension.
 *
 * @return string
 */
function get_html_as_string( $filename ) {
	ob_start();
	require __DIR__ . DIRECTORY_SEPARATOR . "{$filename}.html";
	return ob_get_clean();
}

/**
 * Returns the requested stylesheet.
 *
 * @since 1.0.0
 *
 * @param string $filename Stylesheet filename without the .css extension.
 *
 * @return string
 */
function get_css_as_string( $filename ) {
	ob_start();
	require __DIR__ . DIRECTORY_SEPARATOR . "{$filename}.css";
	return ob_get_clean();
}
