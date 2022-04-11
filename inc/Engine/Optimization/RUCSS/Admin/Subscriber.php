<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;
use WP_Admin_Bar;

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
	 * Queue instance
	 *
	 * @var QueueInterface
	 */
	private $queue;


	/**
	 * Instantiate the class
	 *
	 * @param Settings       $settings Settings instance.
	 * @param Database       $database Database instance.
	 * @param UsedCSS        $used_css UsedCSS instance.
	 * @param QueueInterface $queue    Queue instance.
	 */
	public function __construct( Settings $settings, Database $database, UsedCSS $used_css, QueueInterface $queue ) {
		$this->settings = $settings;
		$this->database = $database;
		$this->used_css = $used_css;
		$this->queue    = $queue;
	}

	/**
	 * Return an array of events that this subscriber listens to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() : array {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'rocket_first_install_options'           => 'add_options_first_time',
			'rocket_input_sanitize'                  => [ 'sanitize_options', 14, 2 ],
			'update_option_' . $slug                 => [
				[ 'clean_used_css_and_cache', 9, 2 ],
				[ 'clean_used_css_with_cdn', 9, 2 ],
				[ 'maybe_set_processing_transient', 50, 2 ],
			],
			'switch_theme'                           => 'truncate_used_css',
			'wp_trash_post'                          => 'delete_used_css_on_update_or_delete',
			'delete_post'                            => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                       => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'                => 'delete_used_css_on_update_or_delete',
			'edit_term'                              => 'delete_term_used_css',
			'pre_delete_term'                        => 'delete_term_used_css',
			'init'                                   => [
				[ 'schedule_clean_not_commonly_used_rows' ],
				[ 'initialize_rucss_queue_runner' ],
			],
			'rocket_rucss_clean_rows_time_event'     => 'cron_clean_rows',
			'admin_post_rocket_clear_usedcss'        => 'truncate_used_css_handler',
			'admin_post_rocket_clear_usedcss_url'    => 'clear_url_usedcss',
			'admin_notices'                          => [
				[ 'clear_usedcss_result' ],
				[ 'display_processing_notice' ],
				[ 'display_success_notice' ],
				[ 'display_as_missed_tables_notice' ],
			],
			'rocket_admin_bar_items'                 => [
				[ 'add_clean_used_css_menu_item' ],
				[ 'add_clear_usedcss_bar_item' ],
			],
			'rocket_before_add_field_to_settings'    => [
				[ 'set_optimize_css_delivery_value', 10, 1 ],
				[ 'set_optimize_css_delivery_method_value', 10, 1 ],
			],
			'rocket_localize_admin_script'           => 'add_localize_script_data',
			'action_scheduler_queue_runner_concurrent_batches' => 'adjust_as_concurrent_batches',
			'pre_update_option_wp_rocket_settings'   => [ 'maybe_disable_combine_css', 11, 2 ],
			'wp_rocket_upgrade'                      => [
				[ 'set_option_on_update', 14, 2 ],
				[ 'update_safelist_items', 15, 2 ],
			],
			'wp_ajax_rocket_spawn_cron'              => 'spawn_cron',
			'rocket_deactivation'                    => 'cancel_queues',
			'shutdown'                               => 'schedule_rucss_pending_jobs_cron',
			'admin_head-tools_page_action-scheduler' => 'delete_as_tables_transient_on_tools_page',
		];
	}

	/**
	 * Cron callback for deleting old rows in both table databases.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function cron_clean_rows() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->database->delete_old_used_css();
		$this->database->delete_old_resources();
	}

	/**
	 * Schedules cron for used CSS.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function schedule_clean_not_commonly_used_rows() {
		if (
			! $this->settings->is_enabled()
			&&
			wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' )
		) {
			wp_clear_scheduled_hook( 'rocket_rucss_clean_rows_time_event' );

			return;
		}

		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		if ( wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) ) {
			return;
		}

		wp_schedule_event( time(), 'weekly', 'rocket_rucss_clean_rows_time_event' );
	}

	/**
	 * Schedule the cron job for RUCSS pending jobs.
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function schedule_rucss_pending_jobs_cron() {
		$error = error_get_last();

		// Delete the transient when any error happens.
		if ( null !== $error ) {
			delete_transient( 'rocket_rucss_as_tables_count' );

			return;
		}

		if ( ! $this->is_valid_as_tables() ) {
			return;
		}

		if ( ! $this->settings->is_enabled() ) {
			if ( ! $this->queue->is_pending_jobs_cron_scheduled() ) {
				return;
			}

			Logger::debug( 'RUCSS: Cancel pending jobs cron job because of disabling RUCSS option.' );

			$this->queue->cancel_pending_jobs_cron();
			return;
		}

		/**
		 * Filters the cron interval.
		 *
		 * @since 3.11
		 *
		 * @param int $interval Interval in seconds.
		 */
		$interval = apply_filters( 'rocket_rucss_pending_jobs_cron_interval', 1 * rocket_get_constant( 'MINUTE_IN_SECONDS', 60 ) );

		Logger::debug( "RUCSS: Schedule pending jobs Cron job with interval {$interval} seconds." );

		$this->queue->schedule_pending_jobs_cron( $interval );
	}

	/**
	 * Initialize the queue runner for our RUCSS.
	 *
	 * @return void
	 */
	public function initialize_rucss_queue_runner() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		RUCSSQueueRunner::instance()->init();
	}

	/**
	 * Checks if Action scheduler tables are there or not.
	 *
	 * @since 3.11.0.3
	 *
	 * @return bool
	 */
	private function is_valid_as_tables() {
		$cached_count = get_transient( 'rocket_rucss_as_tables_count' );
		if ( false !== $cached_count && ! is_admin() ) { // Stop caching in admin UI.
			return 4 === (int) $cached_count;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$found_as_tables = $wpdb->get_col(
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'actionscheduler%' )
		);

		set_transient( 'rocket_rucss_as_tables_count', count( $found_as_tables ), rocket_get_constant( 'DAY_IN_SECONDS', 24 * 60 * 60 ) );

		return 4 === count( $found_as_tables );
	}

	/**
	 * Delete used_css on Update Post or Delete post.
	 *
	 * @since 3.9
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
	 * Deletes the used CSS when updating a term
	 *
	 * @since 3.10.2
	 *
	 * @param int $term_id the term ID.
	 *
	 * @return void
	 */
	public function delete_term_used_css( $term_id ) {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$url = get_term_link( (int) $term_id );

		if ( is_wp_error( $url ) ) {
			return;
		}

		$this->used_css->delete_used_css( untrailingslashit( $url ) );
	}

	/**
	 * Truncate RUCSS used_css DB table.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function truncate_used_css() {
		if ( ! $this->settings->is_enabled() ) {
			return;
		}

		$this->delete_used_css_rows();
		$this->set_notice_transient();
	}

	/**
	 * Deletes the used CSS from the table
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	private function delete_used_css_rows() {
		if ( 0 < $this->used_css->get_not_completed_count() ) {
			$this->used_css->remove_all_completed_rows();
		} else {
			$this->database->truncate_used_css_table();
		}

		/**
		 * Fires after the used CSS has been cleaned in the database
		 *
		 * @since 3.11
		 */
		do_action( 'rocket_after_clean_used_css' );
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
	 * Truncate UsedCSS DB Table when `remove_unused_css_safelist` is changed.
	 *
	 * @since 3.9
	 *
	 * @param array $old_value An array of submitted values for the settings.
	 * @param array $value     An array of previous values for the settings.
	 *
	 * @return void
	 */
	public function clean_used_css_and_cache( $old_value, $value ) {
		if ( ! isset( $value['remove_unused_css_safelist'], $old_value['remove_unused_css_safelist'] ) ) {
			return;
		}

		if ( $value['remove_unused_css_safelist'] === $old_value['remove_unused_css_safelist'] ) {
			return;
		}

		$this->delete_used_css_rows();

		$this->set_notice_transient();
	}

	/**
	 * Truncate UsedCSS DB Table when CDN option is changed.
	 *
	 * @since 3.11
	 *
	 * @param array $old_value An array of submitted values for the settings.
	 * @param array $value     An array of previous values for the settings.
	 *
	 * @return void
	 */
	public function clean_used_css_with_cdn( $old_value, $value ) {
		if ( ! isset( $value['cdn'], $old_value['cdn'] ) ) {
			return;
		}

		if ( empty( $value['remove_unused_css'] ) ) {
			return;
		}

		if (
			$value['cdn'] === $old_value['cdn']
			&&
			$value['cdn_cnames'] === $old_value['cdn_cnames']
			&&
			$value['cdn_zone'] === $old_value['cdn_zone']
		) {
			return;
		}

		$this->delete_used_css_rows();

		$this->set_notice_transient();
	}

	/**
	 * Truncate used_css table when clicking on the dashboard button.
	 *
	 * @since 3.9
	 *
	 * @return void
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
					'message' => sprintf(
						// translators: %1$s = plugin name.
						__( '%1$s: Used CSS option is not enabled!', 'rocket' ),
						'<strong>WP Rocket</strong>'
					),
				]
			);

			wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		$this->delete_used_css_rows();

		rocket_clean_domain();
		rocket_dismiss_box( 'rocket_warning_plugin_modification' );

		set_transient(
			'rocket_clear_usedcss_response',
			[
				'status'  => 'success',
				'message' => sprintf(
					// translators: %1$s = plugin name.
					__( '%1$s: Used CSS cache cleared!', 'rocket' ),
					'<strong>WP Rocket</strong>'
				),
			]
		);

		$this->set_notice_transient();

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Show admin notice after clearing used_css table.
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function clear_usedcss_result() {
		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return;
		}

		if ( ! $this->settings->is_enabled() ) {
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
	 * @param \WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_clean_used_css_menu_item( $wp_admin_bar ) {
		$this->settings->add_clean_used_css_menu_item( $wp_admin_bar );
	}

	/**
	 * Set optimize css delivery value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_value( $field_args ): array {
		return $this->settings->set_optimize_css_delivery_value( $field_args );
	}

	/**
	 * Set optimize css delivery method value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args Array of field to be added to settings page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_method_value( $field_args ): array {
		return $this->settings->set_optimize_css_delivery_method_value( $field_args );
	}

	/**
	 * Displays the RUCSS currently processing notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function display_processing_notice() {
		$this->settings->display_processing_notice();
	}

	/**
	 * Displays the RUCSS success notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function display_success_notice() {
		$this->settings->display_success_notice();
	}

	/**
	 * Display admin notice when detecting any missed Action scheduler tables.
	 *
	 * @since 3.11.0.3
	 *
	 * @return void
	 */
	public function display_as_missed_tables_notice() {
		if ( function_exists( 'get_current_screen' ) && 'tools_page_action-scheduler' === get_current_screen()->id ) {
			return;
		}

		if ( $this->is_valid_as_tables() ) {
			return;
		}

		$this->settings->display_as_missed_tables_notice();
	}

	/**
	 * Adds the notice end time to WP Rocket localize script data
	 *
	 * @since 3.11
	 *
	 * @param array $data Localize script data.
	 * @return array
	 */
	public function add_localize_script_data( $data ): array {
		return $this->settings->add_localize_script_data( $data );
	}

	/**
	 * Clear UsedCSS for the current URL.
	 *
	 * @return void
	 */
	public function clear_url_usedcss() {
		$url = wp_get_referer();

		if ( 0 !== strpos( $url, 'http' ) ) {
			$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
			$url       = $parse_url['scheme'] . '://' . $parse_url['host'] . $url;
		}

		$this->used_css->clear_url_usedcss( $url );

		wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}

	/**
	 * Adjust Action Scheduler to have two concurrent batches on the same time.
	 *
	 * @param int $num Number of concurrent batches.
	 *
	 * @return int
	 */
	public function adjust_as_concurrent_batches( int $num = 1 ) {
		return ( 2 < $num ) ? $num : 2;
	}

	/**
	 * Add clear UsedCSS adminbar item.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Adminbar object.
	 *
	 * @return void
	 */
	public function add_clear_usedcss_bar_item( WP_Admin_Bar $wp_admin_bar ) {
		$this->used_css->add_clear_usedcss_bar_item( $wp_admin_bar );
	}

	/**
	 * Disable combine CSS option when RUCSS is enabled
	 *
	 * @since 3.11
	 *
	 * @param array $value     The new, unserialized option value.
	 * @param array $old_value The old option value.
	 *
	 * @return array
	 */
	public function maybe_disable_combine_css( $value, $old_value ): array {
		return $this->settings->maybe_disable_combine_css( $value, $old_value );
	}

	/**
	 * Disables combine CSS if RUCSS is enabled when updating to 3.11
	 *
	 * @since 3.11
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );

		if ( version_compare( $old_version, '3.11', '>=' ) ) {
			return;
		}

		$this->database->truncate_used_css_table();
		rocket_clean_domain();
		$this->set_notice_transient();

		wp_safe_remote_get(
			home_url(),
			[
				'timeout'    => 0.01,
				'blocking'   => false,
				'user-agent' => 'WP Rocket/Homepage Preload',
				'sslverify'  => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);
	}

	/**
	 * Updates safelist items for new SaaS compatibility
	 *
	 * @since 3.11.0.2
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function update_safelist_items( $new_version, $old_version ) {
		$this->settings->update_safelist_items( $old_version );
	}

	/**
	 * Sets the processing transient if RUCSS is enabled
	 *
	 * @since 3.11
	 *
	 * @param mixed $old_value Option old value.
	 * @param mixed $value     Option new value.
	 *
	 * @return void
	 */
	public function maybe_set_processing_transient( $old_value, $value ) {
		if ( ! isset( $old_value['remove_unused_css'], $value['remove_unused_css'] ) ) {
			return;
		}

		if ( 0 === (int) $value['remove_unused_css'] ) {
			return;
		}

		if ( $old_value['remove_unused_css'] === $value['remove_unused_css'] ) {
			return;
		}

		$this->set_notice_transient();
	}

	/**
	 * Sets the transient for the processing notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	private function set_notice_transient() {
		set_transient(
			'rocket_rucss_processing',
			time() + 90,
			1.5 * MINUTE_IN_SECONDS
		);

		rocket_renew_box( 'rucss_success_notice' );
	}

	/**
	 * Sends a request to run cron when switching to RUCSS completed notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function spawn_cron() {
		check_ajax_referer( 'rocket-ajax', 'nonce' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error();
			return;
		}

		spawn_cron();

		wp_send_json_success();
	}

	/**
	 * Cancel queues and crons for RUCSS.
	 *
	 * @return void
	 */
	public function cancel_queues() {
		$this->queue->cancel_pending_jobs_cron();

		if ( ! wp_next_scheduled( 'rocket_rucss_clean_rows_time_event' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'rocket_rucss_clean_rows_time_event' );
	}

	/**
	 * Delete the transient for Action scheduler once admin visits the AS tools page.
	 *
	 * @return void
	 */
	public function delete_as_tables_transient_on_tools_page() {
		delete_transient( 'rocket_rucss_as_tables_count' );
	}
}
