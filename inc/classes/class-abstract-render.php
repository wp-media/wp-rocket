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
	public function generate( $template, $data = [] ) {
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

	/**
	 * Displays the button template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $type   Type of button (can be button or link).
	 * @param string $action Action to be performed.
	 * @param array  $args   Optional array of arguments to populate the button attributes.
	 * @return void
	 */
	public function render_action_button( $type, $action, $args = [] ) {
		$default = [
			'label'      => '',
			'action'     => '',
			'url'        => '',
			'parameter'  => '',
			'attributes' => '',
		];

		$args = wp_parse_args( $args, $default );

		if ( ! empty( $args['attributes'] ) ) {
			$attributes = '';
			foreach ( $args['attributes'] as $key => $value ) {
				$attributes .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
			}

			$args['attributes'] = $attributes;
		}

		if ( 'link' !== $type ) {
			$args['action'] = $action;
			echo $this->generate( 'buttons/button', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
			return;
		}

		switch ( $action ) {
			case 'ask_support':
			case 'view_account':
				$args['url'] = rocket_get_external_url(
					'ask_support' === $action ? 'support' : 'account',
					[
						'utm_source' => 'wp_plugin',
						'utm_medium' => 'wp_rocket',
					]
				);
				break;
			case 'purge_cache':
			case 'preload':
			case 'rocket_purge_opcache':
			case 'rocket_purge_cloudflare':
			case 'rocket_purge_sucuri':
			case 'rocket_rollback':
			case 'rocket_export':
			case 'rocket_generate_critical_css':
			case 'rocket_purge_rocketcdn':
			case 'rocket_clear_usedcss':
				$url = admin_url( 'admin-post.php?action=' . $action );

				if ( ! empty( $args['parameters'] ) ) {
					$url = add_query_arg( $args['parameters'], $url );
				}

				if ( 'purge_cache' === $action ) {
					$action .= '_all';
				}

				$args['url'] = wp_nonce_url( $url, $action );
				break;
			case 'documentation':
				$args['url'] = get_rocket_documentation_url();
				break;
		}

		echo $this->generate( 'buttons/link', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}
}
