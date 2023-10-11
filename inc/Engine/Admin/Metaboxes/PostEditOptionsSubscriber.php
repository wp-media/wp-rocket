<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\Metaboxes;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class PostEditOptionsSubscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Options instance
	 *
	 * @var Options_data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options Options instance.
	 * @param string       $template_path Path to the views.
	 */
	public function __construct( Options_Data $options, $template_path ) {
		parent::__construct( $template_path );

		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'add_meta_boxes' => 'options_metabox',
			'save_post'      => 'save_metabox_options',
		];
	}

	/**
	 * Add options metabox on post edit page
	 *
	 * @return void
	 */
	public function options_metabox() {
		if ( ! rocket_can_display_options() ) {
			return;
		}

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$cpts = get_post_types(
			[
				'public' => true,
			],
			'objects'
		);

		unset( $cpts['attachment'] );

		/**
		 * Filters the post types to add the options metabox to
		 *
		 * @param array $cpts Array of post types.
		 */
		$cpts = apply_filters( 'rocket_metabox_options_post_types', $cpts );

		foreach ( $cpts as $cpt => $cpt_object ) {
			$label = $cpt_object->labels->singular_name;
			add_meta_box( 'rocket_post_exclude', sprintf( __( 'WP Rocket Options', 'rocket' ), $label ), [ $this, 'display_metabox' ], $cpt, 'side', 'core' );
		}
	}

	/**
	 * Displays checkboxes to de/activate some options
	 *
	 * @return void
	 */
	public function display_metabox() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		global $post, $pagenow;

		$excluded_url = false;

		if ( 'post-new.php' !== $pagenow ) {
			$path = rocket_clean_exclude_file( get_permalink( $post->ID ) );

			if ( in_array( $path, $this->options->get( 'cache_reject_uri', [] ), true ) ) {
				$excluded_url = true;
			}
		}

		$original_fields = [];

		/**
		 * WP Rocket Metabox fields on post edit page.
		 *
		 * @param string[] $original_fields Metaboxes fields.
		 */
		$fields = apply_filters( 'rocket_meta_boxes_fields', $original_fields );

		if ( ! is_array( $fields ) ) {
			$fields = $original_fields;
		}

		$fields_attributes = [];

		foreach ( $fields as $field => $label ) {
			$disabled = disabled( ! $this->options->get( $field ), true, false );

			$fields_attributes[ $field ]['id']    = $field;
			$fields_attributes[ $field ]['label'] = $label;
			// translators: %s is the name of the option.
			$fields_attributes[ $field ]['title']    = $disabled ? ' title="' . esc_attr( sprintf( __( 'Activate first the %s option.', 'rocket' ), $label ) ) . '"' : '';
			$fields_attributes[ $field ]['class']    = $disabled ? ' class="rkt-disabled"' : '';
			$fields_attributes[ $field ]['checked']  = ! $disabled ? checked( ! get_post_meta( $post->ID, '_rocket_exclude_' . $field, true ), true, false ) : '';
			$fields_attributes[ $field ]['disabled'] = $disabled;
		}

		echo $this->generate(  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'post_edit_options',
			[
				'excluded_url' => $excluded_url,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'fields'       => $fields_attributes,  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			]
		);
	}

	/**
	 * Updates the options from the metabox.
	 *
	 * @return void
	 */
	public function save_metabox_options() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['post_ID'], $_POST['rocket_post_exclude_hidden'] ) ) {
			return;
		}

		check_admin_referer( 'rocket_box_option', '_rocketnonce' );

		// No cache field.
		if ( isset( $_POST['post_status'] ) && 'publish' === $_POST['post_status'] ) {
			$new_cache_reject_uri = $cache_reject_uri = $this->options->get( 'cache_reject_uri', [] ); // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			$rejected_uris        = array_flip( $cache_reject_uri );
			$path                 = rocket_clean_exclude_file( get_permalink( (int) $_POST['post_ID'] ) );

			if ( isset( $_POST['rocket_post_nocache'] ) ) {
				if ( ! isset( $rejected_uris[ $path ] ) ) {
					array_push( $new_cache_reject_uri, $path );
				}
			} elseif ( isset( $rejected_uris[ $path ] ) ) {
				unset( $new_cache_reject_uri[ $rejected_uris[ $path ] ] );
			}

			if ( $new_cache_reject_uri !== $cache_reject_uri ) {
				// Update the "Never cache the following pages" option.
				update_rocket_option( 'cache_reject_uri', $new_cache_reject_uri );

				// Update config file.
				rocket_generate_config_file();
			}
		}

		$original_fields = [];

		/**
		 * Metaboxes fields.
		 *
		 * @param string[] $original_fields Metaboxes fields.
		 */
		$fields = apply_filters( 'rocket_meta_boxes_fields', $original_fields );

		if ( ! is_array( $fields ) ) {
			$fields = $original_fields;
		}

		$fields = array_keys( $fields );

		foreach ( $fields as $field ) {
			if ( ! isset( $_POST['rocket_post_exclude_hidden'][ $field ] ) ) {
				continue;
			}

			if ( isset( $_POST['rocket_post_exclude'][ $field ] ) ) {
				delete_post_meta( (int) $_POST['post_ID'], '_rocket_exclude_' . $field );
				continue;
			}

			if ( $this->options->get( $field ) ) {
				update_post_meta( (int) $_POST['post_ID'], '_rocket_exclude_' . $field, true );
			}
		}
	}
}
