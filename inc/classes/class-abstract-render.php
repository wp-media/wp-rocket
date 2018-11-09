<?php
namespace WP_Rocket;

use WP_Rocket\Interfaces\Render_Interface;

/**
 * Handle rendering of HTML content created by WP Rocket.
 *
 * @since 3.0
 * @author Remy Perona
 */
abstract class Abstract_Render implements Render_Interface {
	/**
	 * Path to the templates
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var string
	 */
	private $template_path;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $template_path Path to the templates.
	 */
	public function __construct( $template_path ) {
		$this->template_path = $template_path;
	}

	/**
	 * Renders the given template if it's readable.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $template Template slug.
	 * @param array  $data     Data to pass to the template.
	 */
	public function generate( $template, $data = array() ) {
		$template_path = $this->get_template_path( $template );

		if ( ! rocket_direct_filesystem()->is_readable( $template_path ) ) {
			return;
		}

		ob_start();

		include $template_path;

		return trim( ob_get_clean() );
	}

	/**
	 * Returns the path a specific template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $path Relative path to the template.
	 * @return string
	 */
	private function get_template_path( $path ) {
		return $this->template_path . '/' . $path . '.php';
	}
}
