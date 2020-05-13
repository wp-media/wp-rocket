<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Abstract_Render;

defined( 'ABSPATH' ) || exit;

/**
 * Handle rendering of HTML content for the settings page.
 *
 * @since 3.5.5 Moves into the new architecture.
 * @since 3.0
 */
class Render extends Abstract_render {
	/**
	 * Settings array.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $settings = [];

	/**
	 * Hidden settings array.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $hidden_settings;

	/**
	 * Sets the settings value.
	 *
	 * @since 3.0
	 *
	 * @param array $settings Array of settings.
	 * @return void
	 */
	public function set_settings( $settings ) {
		$this->settings = (array) $settings;
	}

	/**
	 * Sets the hidden settings value.
	 *
	 * @since 3.0
	 *
	 * @param array $hidden_settings Array of hidden settings.
	 */
	public function set_hidden_settings( $hidden_settings ) {
		$this->hidden_settings = $hidden_settings;
	}

	/**
	 * Renders the page sections navigation.
	 *
	 * @since 3.0
	 */
	public function render_navigation() {
		/**
		 * Filters WP Rocket settings page navigation items.
		 *
		 * @since 3.0
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
		$navigation = (array) apply_filters( 'rocket_settings_menu_navigation', $this->settings );

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

		echo $this->generate( 'navigation', $navigation ); // phpcs:ignore WordPress.Security.EscapeOutput -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Render the page sections.
	 *
	 * @since 3.0
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

			echo $this->generate( 'page-sections/' . $id, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
		}
	}

	/**
	 * Render the Imagify page section.
	 *
	 * @since 3.2
	 */
	public function render_imagify_section() {
		echo $this->generate( 'page-sections/imagify' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Render the Tutorials page section.
	 *
	 * @since 3.4
	 */
	public function render_tutorials_section() {
		echo $this->generate( 'page-sections/tutorials' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Render the tools page section.
	 *
	 * @since 3.0
	 */
	public function render_tools_section() {
		echo $this->generate( 'page-sections/tools' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Renders the settings sections for a page section.
	 *
	 * @since 3.0
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

			call_user_func_array( [ $this, $args['type'] ], [ $args ] );
		}
	}

	/**
	 * Renders the settings fields for a setting section and page.
	 *
	 * @since 3.0
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

			call_user_func_array( [ $this, $args['type'] ], [ $args ] );
		}
	}

	/**
	 * Renders hidden fields in the form.
	 *
	 * @since 3.0
	 */
	public function render_hidden_fields() {
		foreach ( $this->hidden_settings as $setting ) {
			call_user_func_array( [ $this, 'hidden' ], [ $setting ] );
		}
	}

	/**
	 * Displays the fields container section template.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function fields_container( $args ) {
		echo $this->generate( 'sections/fields-container', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the no container section template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function nocontainer( $args ) {
		echo $this->generate( 'sections/nocontainer', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the add-ons container section template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function addons_container( $args ) {
		echo $this->generate( 'sections/addons-container', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the text field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function text( $args ) {
		echo $this->generate( 'fields/text', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
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
		echo $this->generate( 'fields/checkbox', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the textarea field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function textarea( $args ) {
		if ( is_array( $args['value'] ) ) {
			$args['value'] = implode( "\n", $args['value'] );
		}

		$args['value'] = empty( $args['value'] ) ? '' : $args['value'];

		echo $this->generate( 'fields/textarea', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the sliding checkbox field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function sliding_checkbox( $args ) {
		echo $this->generate( 'fields/sliding-checkbox', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the number input field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function number( $args ) {
		echo $this->generate( 'fields/number', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the select field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function select( $args ) {
		echo $this->generate( 'fields/select', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the clear cache lifespan block template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function cache_lifespan( $args ) {
		echo $this->generate( 'fields/cache-lifespan', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the hidden field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function hidden( $args ) {
		echo $this->generate( 'fields/hidden', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the CDN CNAMES template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function cnames( $args ) {
		echo $this->generate( 'fields/cnames', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the RocketCDN template.
	 *
	 * @since 3.5
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocket_cdn( $args ) {
		echo $this->generate( 'fields/rocket-cdn', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the one-click add-on field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function one_click_addon( $args ) {
		echo $this->generate( 'fields/one-click-addon', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the Rocket add-on field template.
	 *
	 * @since 3.0
	 *
	 * @param array $args Array of arguments to populate the template.
	 */
	public function rocket_addon( $args ) {
		echo $this->generate( 'fields/rocket-addon', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays the import form template.
	 *
	 * @since 3.0
	 */
	public function render_import_form() {
		$args = [];

		/**
		 * Filter the maximum allowed upload size for import files.
		 *
		 * @since (WordPress) 2.3.0
		 *
		 * @see wp_max_upload_size()
		 *
		 * @param int $max_upload_size Allowed upload size. Default 1 MB.
		 */
		$args['bytes']       = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$args['size']        = size_format( $args['bytes'] );
		$args['upload_dir']  = wp_upload_dir();
		$args['action']      = 'rocket_import_settings';
		$args['submit_text'] = __( 'Upload file and import settings', 'rocket' );

		echo $this->generate( 'fields/import-form', $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Displays a partial template.
	 *
	 * @since 3.0
	 *
	 * @param string $part Partial template name.
	 */
	public function render_part( $part ) {
		echo $this->generate( 'partials/' . $part ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}
}
