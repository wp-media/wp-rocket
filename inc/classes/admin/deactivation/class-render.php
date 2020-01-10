<?php
namespace WP_Rocket\Admin\Deactivation;

use WP_Rocket\Abstract_Render;

defined( 'ABSPATH' ) || exit;

/**
 * Handles rendering of deactivation intent form on plugins page
 *
 * @since 3.0
 * @author Remy Perona
 */
class Render extends Abstract_Render {
	/**
	 * Renders Deactivation intent form
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function render_form() {
		$args = [
			'deactivation_url' => wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . rawurlencode( 'wp-rocket/wp-rocket.php' ), 'deactivate-plugin_wp-rocket/wp-rocket.php' ),
		];

		echo $this->generate( 'form', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}
}
