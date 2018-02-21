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
		$navigation = array_map(
			function( array $item ) {
				unset( $item['sections'] );
				return $item;
			},
			$this->settings
		);

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
		 *     @type string $title            Menu title.
		 *     @type string $menu_description Menu description.
		 * }
		 */
		$navigation = apply_filters( 'rocket_settings_menu_navigation', $navigation );

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
			$id = str_replace( '_', '-', $id );
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

		foreach ( $this->settings[ $page ]['sections'] as $settings_section ) {
			call_user_func_array( array( $this, $settings_section['type'] ), array( $settings_section ) );
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

		foreach ( $this->settings[ $page ]['sections'][ $section ]['fields'] as $field ) {
			call_user_func_array( array( $this, $field['type'] ), array( $field ) );
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
		if ( isset( $args['input_attr'] ) ) {
			$input_attr = '';

			foreach ( $args['input_attr'] as $key => $value ) {
				if ( 'disabled' === $key ) {
					if ( 1 === $value ) {
						$input_attr .= ' disabled';
					}

					continue;
				}

				$input_attr .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}

			$args['input_attr'] = $input_attr;
		}

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

		$args['value'] = ! empty( $args['value'] ) ? $args['value'] : '';

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
}
