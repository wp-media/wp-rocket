<?php

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

class Divi extends ThirdpartyTheme {
	/**
	 * Theme name
	 *
	 * @var string
	 */
	protected static $theme_name = 'divi';

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Delay JS HTML class.
	 *
	 * @var HTML
	 */
	private $delayjs_html;

	/**
	 * Used CSS controller instance.
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Instantiate the class
	 *
	 * @param Options      $options_api Options API instance.
	 * @param Options_Data $options     WP Rocket options instance.
	 * @param HTML         $delayjs_html DelayJS HTML class.
	 * @param UsedCSS      $used_css Used CSS controller instance.
	 */
	public function __construct( Options $options_api, Options_Data $options, HTML $delayjs_html, UsedCSS $used_css ) {
		$this->options_api  = $options_api;
		$this->options      = $options;
		$this->delayjs_html = $delayjs_html;
		$this->used_css     = $used_css;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'switch_theme'                    => [ 'maybe_disable_youtube_preview', PHP_INT_MAX, 2 ],
			'rocket_specify_dimension_images' => 'disable_image_dimensions_height_percentage',
		];

		if ( ! self::is_current_theme() ) {
			return $events;
		}
		$events['rocket_exclude_js']                            = 'exclude_js';
		$events['rocket_maybe_disable_youtube_lazyload_helper'] = 'add_divi_to_description';

		$events['wp_enqueue_scripts'] = 'disable_divi_jquery_body';

		$events['wp']                = 'disable_dynamic_css_on_rucss';
		$events['after_setup_theme'] = 'remove_assets_generated';
		$events['et_save_post']      = 'handle_save_template';
		$events['admin_notices']     = 'handle_divi_admin_notice';

		$slug                               = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		$events[ 'update_option_' . $slug ] = [ 'remove_transient_when_enabling_rucss_option', 10, 2 ];

		$events['rocket_after_clean_used_css']       = 'clear_divi_notice';
		$events['wp_ajax_et_theme_builder_api_save'] = [ 'show_rucss_notice_with_save_changes', 9 ];

		return $events;
	}

	/**
	 * Excludes Divi's Salvatorre script from JS minification
	 *
	 * Prevent an error after minification/concatenation
	 *
	 * @since 3.6.3
	 *
	 * @param array $excluded_js An array of JS paths to be excluded.
	 *
	 * @return array the updated array of paths
	 */
	public function exclude_js( $excluded_js ) {
		if ( ! rocket_get_constant( 'ET_BUILDER_URI' ) ) {
			return $excluded_js;
		}

		$excluded_js[] = str_replace( home_url(), '', rocket_get_constant( 'ET_BUILDER_URI' ) . '/scripts/salvattore.min.js' );

		return $excluded_js;
	}

	/**
	 * Disables the Replace Youtube iframe by preview thumbnail option if new theme (or parent) is Divi
	 *
	 * @since 3.6.3
	 *
	 * @param string   $name  Name of the new theme.
	 * @param WP_Theme $theme instance of the new theme.
	 *
	 * @return void
	 */
	public function maybe_disable_youtube_preview( $name, $theme ) {
		if ( ! self::is_current_theme( $theme ) ) {
			return;
		}

		$this->options->set( 'lazyload_youtube', 0 );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Adds Divi to the array of items disabling Youtube lazyload
	 *
	 * @since 3.6.3
	 *
	 * @param array $disable_youtube_lazyload Array of items names.
	 *
	 * @return array
	 */
	public function add_divi_to_description( $disable_youtube_lazyload ) {
		if ( ! self::is_current_theme() ) {
			return $disable_youtube_lazyload;
		}

		$disable_youtube_lazyload[] = 'Divi';

		return $disable_youtube_lazyload;
	}

	/**
	 * Disables setting explicit dimensions on images where Divi calculates height as percentage.
	 *
	 * @since 3.8.2
	 *
	 * @param array $images The array of images selected for adding image dimensions.
	 *
	 * @return array The array without images using data-height-percentage.
	 */
	public function disable_image_dimensions_height_percentage( array $images ) {
		foreach ( $images as $key => $image ) {
			if ( false !== strpos( strtolower( $image ), 'data-height-percentage' ) ) {
				unset( $images[ $key ] );
			}
		}

		return $images;
	}

	/**
	 * Disable divi jquery body.
	 *
	 * @since 3.9.3
	 */
	public function disable_divi_jquery_body() {
		if (
			$this->delayjs_html->is_allowed()
			&& defined( 'ET_CORE_VERSION' )
			&& version_compare( ET_CORE_VERSION, '4.10', '>=' )
		) {

			add_filter( 'et_builder_enable_jquery_body', '__return_false' );
		}
	}

	/**
	 * Disable Divi dynamic CSS when RUCSS is activated
	 *
	 * @return void
	 */
	public function disable_dynamic_css_on_rucss() {
		if ( ! $this->options->get( 'remove_unused_css', false ) ) {
			return;
		}
		add_filter( 'et_use_dynamic_css', '__return_false' );
	}

	/**
	 * Remove dynamic late assets action.
	 *
	 * @return void
	 */
	public function remove_assets_generated() {
		remove_all_actions( 'et_dynamic_late_assets_generated' );
	}

	/**
	 * Get layout IDs for a template.
	 *
	 * @param int $template_post_id Template post ID.
	 *
	 * @return array
	 */
	private function get_layout_ids( $template_post_id ) {
		$allowed_post_types = [
			'et_header_layout',
			'et_footer_layout',
			'et_body_layout',
		];
		$current_post_type  = get_post_type( $template_post_id );

		if ( ! in_array( $current_post_type, $allowed_post_types, true ) ) {
			return [];
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (array) $wpdb->get_col(
			$wpdb->prepare(
				'SELECT post_id from ' . $wpdb->postmeta . ' WHERE meta_key = %s AND meta_value = %d',
				'_' . $current_post_type . '_id',
				$template_post_id
			)
		);
	}

	/**
	 * Is allowed for RUCSS notice functionality when saving templates.
	 *
	 * @return bool
	 */
	private function is_allowed_for_rucss() {
		return $this->options->get( 'remove_unused_css', 0 )
			&&
			current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Save template handler.
	 *
	 * @param int $template_post_id Template post ID.
	 *
	 * @return void
	 */
	public function handle_save_template( $template_post_id ) {
		/**
		 * Filters Bypassing saving template functionality.
		 *
		 * @param bool $bypass Bypass save template functionality.
		 * @param int  $template_post_id Currently saved template post id.
		 */
		if ( apply_filters( 'rocket_divi_bypass_save_template', false, $template_post_id ) ) {
			return;
		}

		// If the transient is there, we don't need to do the check again as the admin notice will be there already.
		if ( false !== get_transient( 'rocket_divi_notice' ) ) {
			return;
		}

		$layout_post_ids = $this->get_layout_ids( $template_post_id );

		if ( empty( $layout_post_ids ) ) {
			return;
		}

		foreach ( $layout_post_ids as $layout_post_id ) {
			if ( 'publish' !== get_post_status( $layout_post_id ) ) {
				continue;
			}

			set_transient( 'rocket_divi_notice', true );
			return;
		}
	}

	/**
	 * Admin notices handler.
	 *
	 * @return void
	 */
	public function handle_divi_admin_notice() {
		if ( ! $this->is_allowed_for_rucss() ) {
			return;
		}

		$notice = get_transient( 'rocket_divi_notice' );

		if ( ! $notice ) {
			return;
		}

		rocket_notice_html(
			[
				'status'         => 'warning',
				'dismissible'    => '',
				'dismiss_button' => 'rocket_divi_notice',
				'action'         => 'clear_used_css',
				'message'        =>
					sprintf( '%1$sWP Rocket:%2$s ', '<strong>', '</strong>' ) . // Splitting it because I think the plugin name is not a translatable string.
					esc_html__( 'Your Divi template was updated. Clear the Used CSS if the layout, design or CSS styles were changed.', 'rocket' ),
			]
		);
	}

	/**
	 * Clear divi notice.
	 *
	 * @return void
	 */
	public function clear_divi_notice() {
		delete_transient( 'rocket_divi_notice' );
	}

	/**
	 * Remove notice transient when enabling RUCSS.
	 *
	 * @param array $old_value An array of submitted values for the settings.
	 * @param array $new_value An array of previous values for the settings.
	 *
	 * @return void
	 */
	public function remove_transient_when_enabling_rucss_option( $old_value, $new_value ) {
		if ( ! isset( $new_value['remove_unused_css'], $old_value['remove_unused_css'] ) ) {
			return;
		}

		if ( $new_value['remove_unused_css'] === $old_value['remove_unused_css'] ) {
			return;
		}

		if ( ! $new_value['remove_unused_css'] ) {
			return;
		}

		if ( $this->used_css->has_one_completed_row_at_least() ) {
			return;
		}

		$this->clear_divi_notice();
	}

	/**
	 * Set the transient when save changes button is clicked.
	 *
	 * @return void
	 */
	public function show_rucss_notice_with_save_changes() {
		set_transient( 'rocket_divi_notice', true );
	}
}
