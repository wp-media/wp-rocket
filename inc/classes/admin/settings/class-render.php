<?php
namespace WP_Rocket\Admin\Settings;

use WP_Rocket\Abstract_Render;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handle rendering of HTML content for the settings page.
 *
 * @since 3.0
 * @author Remy Perona
 */
class Render extends Abstract_render {
	/**
	 * Settings array
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Hidden settings array
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $hidden_settings;

	/**
	 * Sets the settings value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $settings Array of settings.
	 * @return void
	 */
	public function set_settings( $settings ) {
		$this->settings = (array) $settings;
	}

	/**
	 * Sets the hidden settings value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $hidden_settings Array of hidden settings.
	 * @return void
	 */
	public function set_hidden_settings( $hidden_settings ) {
		$this->hidden_settings = $hidden_settings;
	}

	/**
	 * Renders the page sections navigation
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function render_navigation() {
		/**
		 * Filters WP Rocket settings page navigation items.
		 *
		 * @since 3.0
		 * @author Remy Perona
		 *
		 * @param array $navigation {
		 *     Items to populate the navigation.
		 *
		 *     @type string $id               Page section identifier.
		 *     @type string $title            Menu item title.
		 *     @type string $menu_description Menu item description.
		 *     @type string $class            Menu item classes
		 * }
		 */
		$navigation = apply_filters( 'rocket_settings_menu_navigation', $this->settings );

		$default = [
			'id'               => '',
			'title'            => '',
			'menu_description' => '',
			'class'            => '',
		];

		$navigation = array_map(
			function( array $item ) use ( $default ) {
				$item = wp_parse_args( $item, $default );

				if ( ! empty( $item['class'] ) ) {
					$item['class'] = implode( ' ', array_map( 'sanitize_html_class', $item['class'] ) );
				}

				unset( $item['sections'] );
				return $item;
			},
			$navigation
		);

		echo $this->generate( 'navigation', $navigation );
	}

	/**
	 * Render the page sections.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 */
	public function render_form_sections() {
		if ( ! isset( $this->settings ) ) {
			return;
		}

		foreach ( $this->settings as $id => $args ) {
			$default = [
				'title'            => '',
				'menu_description' => '',
				'class'            => '',
			];

			$args = wp_parse_args( $args, $default );
			$id   = str_replace( '_', '-', $id );

			echo $this->generate( 'page-sections/' . $id, $args );
		}
	}

	/**
	 * Render the tools page section.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 */
	public function render_tools_section() {
		echo $this->generate( 'page-sections/tools' );
	}

	/**
	 * Renders the settings sections for a page section.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $page Page section identifier.
	 * @return void
	 */
	public function render_settings_sections( $page ) {
		if ( ! isset( $this->settings[ $page ]['sections'] ) ) {
			return;
		}

		foreach ( $this->settings[ $page ]['sections'] as $args ) {
			$default = [
				'type'        => 'fields_container',
				'title'       => '',
				'description' => '',
				'class'       => '',
				'help'        => '',
				'helper'      => '',
				'page'        => '',
			];

			$args = wp_parse_args( $args, $default );

			if ( ! empty( $args['class'] ) ) {
				$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
			}

			call_user_func_array( array( $this, $args['type'] ), array( $args ) );
		}
	}

	/**
	 * Renders the settings fields for a setting section and page.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $page    Page section identifier.
	 * @param string $section Settings section identifier.
	 * @return void
	 */
	public function render_settings_fields( $page, $section ) {
		if ( ! isset( $this->settings[ $page ]['sections'][ $section ]['fields'] ) ) {
			return;
		}

		foreach ( $this->settings[ $page ]['sections'][ $section ]['fields'] as $args ) {
			$default = [
				'type'              => 'text',
				'label'             => '',
				'description'       => '',
				'class'             => '',
				'container_class'   => '',
				'default'           => '',
				'helper'            => '',
				'placeholder'       => '',
				'parent'            => '',
				'section'           => '',
				'page'              => '',
				'sanitize_callback' => 'sanitize_text_field',
				'input_attr'        => '',
				'warning'           => [],
			];

			$args = wp_parse_args( $args, $default );

			if ( ! empty( $args['input_attr'] ) ) {
				$input_attr = '';

				foreach ( $args['input_attr'] as $key => $value ) {
					if ( 'disabled' === $key ) {
						if ( 1 === $value ) {
							$input_attr .= ' disabled';
						}

						continue;
					}

					$input_attr .= ' ' . sanitize_key( $key ) . '="' . esc_attr( $value ) . '"';
				}

				$args['input_attr'] = $input_attr;
			}

			if ( ! empty( $args['parent'] ) ) {
				$args['parent'] = ' data-parent="' . esc_attr( $args['parent'] ) . '"';
			}

			if ( ! empty( $args['class'] ) ) {
				$args['class'] = implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) );
			}

			if ( ! empty( $args['container_class'] ) ) {
				$args['container_class'] = implode( ' ', array_map( 'sanitize_html_class', $args['container_class'] ) );
			}

			call_user_func_array( array( $this, $args['type'] ), array( $args ) );
		}
	}

	/**
	 * Renders hidden fields in the form.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function render_hidden_fields() {
		foreach ( $this->hidden_settings as $setting ) {
			call_user_func_array( array( $this, 'hidden' ), array( $setting ) );
		}
	}

	/**
	 * Displays the fields container section template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function fields_container( $args ) {
		echo $this->generate( 'sections/fields-container', $args );
	}

	/**
	 * Displays the no container section template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function nocontainer( $args ) {
		echo $this->generate( 'sections/nocontainer', $args );
	}

	/**
	 * Displays the add-ons container section template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function addons_container( $args ) {
		echo $this->generate( 'sections/addons-container', $args );
	}

	/**
	 * Displays the text field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function text( $args ) {
		echo $this->generate( 'fields/text', $args );
	}

	/**
	 * Displays the checkbox field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function checkbox( $args ) {
		echo $this->generate( 'fields/checkbox', $args );
	}

	/**
	 * Displays the textarea field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function textarea( $args ) {
		if ( is_array( $args['value'] ) ) {
			$args['value'] = implode( "\n", $args['value'] );
		}

		$args['value'] = empty( $args['value'] ) ? '' : $args['value'];

		echo $this->generate( 'fields/textarea', $args );
	}

	/**
	 * Displays the sliding checkbox field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function sliding_checkbox( $args ) {
		echo $this->generate( 'fields/sliding-checkbox', $args );
	}

	/**
	 * Displays the number input field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function number( $args ) {
		echo $this->generate( 'fields/number', $args );
	}

	/**
	 * Displays the select field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function select( $args ) {
		echo $this->generate( 'fields/select', $args );
	}

	/**
	 * Displays the clear cache lifespan block template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function cache_lifespan( $args ) {
		echo $this->generate( 'fields/cache-lifespan', $args );
	}

	/**
	 * Displays the hidden field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function hidden( $args ) {
		echo $this->generate( 'fields/hidden', $args );
	}

	/**
	 * Displays the CDN CNAMES template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function cnames( $args ) {
		echo $this->generate( 'fields/cnames', $args );
	}

	/**
	 * Displays the one-click add-on field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function one_click_addon( $args ) {
		echo $this->generate( 'fields/one-click-addon', $args );
	}

	/**
	 * Displays the Rocket add-on field template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 * @return void
	 */
	public function rocket_addon( $args ) {
		echo $this->generate( 'fields/rocket-addon', $args );
	}

	/**
	 * Displays the import form template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function render_import_form() {
		$args = array();

		/**
		 * Filter the maximum allowed upload size for import files.
		 *
		 * @since (WordPress) 2.3.0
		 *
		 * @see wp_max_upload_size()
		 *
		 * @param int $max_upload_size Allowed upload size. Default 1 MB.
		 */
		$args['bytes']       = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // Filter from WP Core.
		$args['size']        = size_format( $args['bytes'] );
		$args['upload_dir']  = wp_upload_dir();
		$args['action']      = 'rocket_import_settings';
		$args['submit_text'] = __( 'Upload file and import settings', 'rocket' );

		echo $this->generate( 'fields/import-form', $args );
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
	public function render_action_button( $type, $action, $args = array() ) {
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

		switch ( $type ) {
			case 'link':
				switch ( $action ) {
					case 'ask_support':
						$args['url'] = rocket_get_external_url( 'support', array(
							'utm_source' => 'wp_plugin',
							'utm_medium' => 'wp_rocket',
						) );
					break;
					case 'view_account':
						$args['url'] = rocket_get_external_url( 'account', array(
							'utm_source' => 'wp_plugin',
							'utm_medium' => 'wp_rocket',
						) );
						break;
					case 'purge_cache':
						$url = admin_url( 'admin-post.php?action=' . $action );

						if ( isset( $args['parameters'] ) ) {
							$url = add_query_arg( $args['parameters'], $url );
						}

						$args['url'] = wp_nonce_url( $url, $action . '_all' );
						break;
					case 'preload':
					case 'rocket_purge_opcache':
					case 'rocket_purge_cloudflare':
					case 'rocket_rollback':
					case 'rocket_export':
					case 'rocket_generate_critical_css':
						$url = admin_url( 'admin-post.php?action=' . $action );

						if ( ! empty( $args['parameters'] ) ) {
							$url = add_query_arg( $args['parameters'], $url );
						}

						$args['url'] = wp_nonce_url( $url, $action );
						break;
					case 'documentation':
						$args['url'] = get_rocket_documentation_url();
						break;
				}

				echo $this->generate( 'buttons/link', $args );
				break;
			default:
				$args['action'] = $action;
				echo $this->generate( 'buttons/button', $args );
				break;
		}
	}

	/**
	 * Displays a partial template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $part Partial template name.
	 *
	 * @return void
	 */
	public function render_part( $part ) {
		echo $this->generate( 'partials/' . $part );
	}
}
