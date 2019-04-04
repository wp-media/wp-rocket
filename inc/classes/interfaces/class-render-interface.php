<?php
namespace WP_Rocket\Interfaces;

/**
 * Render interface
 *
 * @since 3.0
 * @author Remy Perona
 */
interface Render_Interface {
	/**
	 * Renders the given template if it's readable.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $template Template slug.
	 * @param array  $data     Data to pass to the template.
	 */
	public function generate( $template, $data );
}
