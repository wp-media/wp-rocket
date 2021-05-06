<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Settings instance
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Database instance
	 *
	 * @var Database
	 */
	private $database;

	/**
	 * UsedCSS instance
	 *
	 * @var UsedCSS
	 */
	private $used_css;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Settings instance.
	 * @param Database $database Database instance.
	 * @param UsedCSS  $used_css UsedCSS instance.
	 */
	public function __construct( Settings $settings, Database $database, UsedCSS $used_css ) {
		$this->settings = $settings;
		$this->database = $database;
		$this->used_css = $used_css;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'rocket_first_install_options'       => 'add_options_first_time',
			'rocket_input_sanitize'              => [ 'sanitize_options', 14, 2 ],
			'update_option_' . $slug             => [ 'clean_used_css_and_cache', 10, 2 ],
			'switch_theme'                       => 'truncate_used_css',
			'rocket_rucss_file_changed'          => 'truncate_used_css',
			'wp_trash_post'                      => 'delete_used_css_on_update_or_delete',
			'delete_post'                        => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                   => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'            => 'delete_used_css_on_update_or_delete',
			'init'                               => 'schedule_clean_not_commonly_used_rows',
			'rocket_rucss_clean_rows_time_event' => 'cron_clean_rows',
			'admin_post_rocket_clear_usedcss'    => 'truncate_used_css_handler',
			'admin_notices'                      => 'clear_usedcss_result',
			'rocket_admin_bar_items'             => 'add_clean_used_css_menu_item',
			'rocket_after_settings_checkbox'     => 'display_progress_bar',
			'admin_enqueue_scripts'              => 'add_admin_js',
		];
	}

	/**
	 * Enqueue React params and Progress bar.
	 *
	 * @return void
	 */
	public function add_admin_js() {
		// Retun on all pages but WP Rocket settings page.
		$screen = get_current_screen();
		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		wp_enqueue_script( 'wpr-rucss-progress-bar', WP_ROCKET_ASSETS_JS_URL . 'react/rucss_progress_bar.js', [ 'react-dom' ], WP_ROCKET_VERSION, true );

		wp_localize_script(
			'wpr-rucss-progress-bar',
			'rocket_rucss_ajax_data',
			[
				'api_url'                => rest_url( 'wp-rocket/v1/rucss/warmup/status' ),
				'api_nonce'              => wp_create_nonce( 'rocket-ajax' ),
				'wpr_rucss_translations' => $this->ui_translations(),
			]
		);
	}

	/**
	 * Cron callback for deleting old rows in both table databases.
	 * Deletes used css files and also cache file for old used css.
	 *
	 * @return void
	 */
	public function cron_clean_rows() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$old_used_css_ids = $this->database->get_old_used_css();
		foreach ( $old_used_css_ids as $old_used_css ) {
			$used_css_item = new UsedCSS_Row( $old_used_css );
			// Delete file from filesystem.
			$this->used_css->delete_used_css_file( $used_css_item );
		}

		$this->database->delete_old_used_css();
		$this->database->delete_old_resources();
	}

	/**
	 * Schedules cron for used CSS.
	 *
	 * @return void
	 */
	public function schedule_clean_not_commonly_used_rows() {
		if ( ! $this->settings->is_enabled() ) {
			wp_clear_scheduled_hook( 'rocket_rucss_clean_rows_time_event' );

			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'rocket_rucss_clean_rows_time_event' );
	}

	/**
	 * Delete used_css on Update Post or Delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_used_css_on_update_or_delete( $post_id ) {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( false === $url ) {
			return;
		}

		$this->used_css->delete_used_css( untrailingslashit( $url ) );
	}

	/**
	 * Truncate RUCSS used_css DB table.
	 *
	 * @return void
	 */
	public function truncate_used_css() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->database->truncate_used_css_table();
	}

	/**
	 * Add the RUCSS options to the WP Rocket options array.
	 *
	 * @since 3.9
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_options_first_time( $options ) : array {
		return $this->settings->add_options( $options );
	}

	/**
	 * Sanitizes RUCSS options values when the settings form is submitted
	 *
	 * @since 3.9
	 *
	 * @param array         $input    Array of values submitted from the form.
	 * @param AdminSettings $settings Settings class instance.
	 *
	 * @return array
	 */
	public function sanitize_options( $input, AdminSettings $settings ) : array {
		return $this->settings->sanitize_options( $input, $settings );
	}

	/**
	 * Truncate UsedCSS DB Table and WP Rocket cache when `remove_unused_css_safelist` is changed.
	 *
	 * @since 3.9
	 *
	 * @param array $old_value An array of submitted values for the settings.
	 * @param array $value     An array of previous values for the settings.
	 *
	 * @return void
	 */
	public function clean_used_css_and_cache( $old_value, $value ) {
		if ( ! current_user_can( 'rocket_manage_options' )
			||
			! $this->settings->is_enabled()
		) {
			return;
		}

		if (
			isset( $value['remove_unused_css_safelist'], $old_value['remove_unused_css_safelist'] )
			&&
			$value['remove_unused_css_safelist'] !== $old_value['remove_unused_css_safelist']
		) {
			$this->database->truncate_used_css_table();
			// Clear all caching files.
			rocket_clean_domain();
		}
	}

	/**
	 * Truncate used_css table when clicking on the dashboard button.
	 *
	 * @since 3.9
	 */
	public function truncate_used_css_handler() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_clear_usedcss' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		if ( ! $this->settings->is_enabled() ) {
			set_transient(
				'rocket_clear_usedcss_response',
				[
					'status'  => 'error',
					'message' => __( 'Used CSS option is not enabled!', 'rocket' ),
				]
			);

			wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		$this->database->truncate_used_css_table();
		rocket_clean_domain();

		set_transient(
			'rocket_clear_usedcss_response',
			[
				'status'  => 'success',
				'message' => __( 'Used CSS cache cleared!', 'rocket' ),
			]
		);

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Show admin notice after clearing used_css table.
	 */
	public function clear_usedcss_result() {
		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return;
		}

		$response = get_transient( 'rocket_clear_usedcss_response' );
		if ( ! $response ) {
			return;
		}

		delete_transient( 'rocket_clear_usedcss_response' );

		rocket_notice_html( $response );
	}

	/**
	 * Add Clean used CSS link to WP Rocket admin bar item
	 *
	 * @since 3.9
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_used_css_menu_item( $wp_admin_bar ) {
		$this->settings->add_clean_used_css_menu_item( $wp_admin_bar );
	}

	/**
	 * Displays the RUCSS progressbar
	 *
	 * @since 3.9
	 *
	 * @param string $field_id ID of the settings field.
	 *
	 * @return void
	 */
	public function display_progress_bar( $field_id ) {
		if ( 'remove_unused_css' !== $field_id ) {
			return;
		}

		$this->settings->display_progress_bar();
	}

	/**
	 * Array with UI translations.
	 *
	 * @return array
	 */
	private function ui_translations() : array {
		return [
			'step1_txt'      => __( 'Collected resource files from {count} of {total} key pages.', 'rocket' ),
			'step2_txt'      => __( 'Processed {count} of {total} resource files found on key pages', 'rocket' ),
			'rucss_working'  => __( 'Remove Unused CSS is working!', 'rocket' ),
			'rucss_btn'      => __( 'Generate Used CSS now', 'rocket' ),
			'warmed_list'    => __( 'Files which could not be processed:', 'rocket' ),
			'rucss_info_txt' => sprintf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				__( 'We are processing the CSS on your site. This may take several minutes to complete. %1$sMore info.%2$s', 'rocket' ), '<a href="#" target=_"blank">', '</a>'
			),
			'rucss_btn_txt'  => __( 'We\'ll keep trying to process the missing resources for 1 hour. Meanwhile the removal of Unused CSS will be incomplete and PageSpeed may still show a warning.', 'rocket' ),
		];
	}
}
