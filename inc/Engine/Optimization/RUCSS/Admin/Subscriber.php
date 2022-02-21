<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Event_Manager_Aware_Subscriber_Interface {
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
	 * @param Settings $settings    Settings instance.
	 * @param Database $database    Database instance.
	 * @param UsedCSS  $used_css    UsedCSS instance.
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
			'rocket_first_install_options'        => 'add_options_first_time',
			'rocket_input_sanitize'               => [ 'sanitize_options', 14, 2 ],
			'update_option_' . $slug              => [
				[ 'clean_used_css_and_cache', 10, 2 ],
			],
			'switch_theme'                        => 'truncate_used_css',
			'rocket_rucss_file_changed'           => 'truncate_used_css',
			'wp_trash_post'                       => 'delete_used_css_on_update_or_delete',
			'delete_post'                         => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                    => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'             => 'delete_used_css_on_update_or_delete',
			'edit_term'                           => 'delete_term_used_css',
			'pre_delete_term'                     => 'delete_term_used_css',
			'init'                                => [
				[ 'schedule_clean_not_commonly_used_rows' ],
				[ 'schedule_rucss_pending_jobs_cron' ],
			],
			'rocket_rucss_clean_rows_time_event'  => 'cron_clean_rows',
			'admin_post_rocket_clear_usedcss'     => 'truncate_used_css_handler',
			'admin_post_rocket_clear_usedcss_url' => 'clear_url_usedcss',
			'admin_notices'                       => 'clear_usedcss_result',
			'rocket_admin_bar_items'              => 'add_clean_used_css_menu_item',
			'rocket_before_add_field_to_settings' => [
				[ 'set_optimize_css_delivery_value', 10, 1 ],
				[ 'set_optimize_css_delivery_method_value', 10, 1 ],
			],
			'admin_init'                          => 'add_rucss_column_status',
			'action_scheduler_queue_runner_concurrent_batches' => 'adjust_as_concurrent_batches',
		];
	}

	/**
	 * Sets the event manager for the subscriber.
	 *
	 * @param Event_Manager $event_manager Event Manager instance.
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * Add RUCSS status column for all public posts/terms table.
	 *
	 * @return void
	 */
	public function add_rucss_column_status() {
		$taxonomies = get_taxonomies(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$post_types = get_post_types(
			[
				'public'             => true,
				'publicly_queryable' => true,
			]
		);

		$post_types[] = 'page';

		foreach ( $post_types as $post_type ) {
			$this->event_manager->add_callback( "manage_{$post_type}_posts_columns", [ $this, 'add_status_column' ] );
			$this->event_manager->add_callback( "manage_{$post_type}_posts_custom_column", [ $this, 'add_status_data' ], 10, 2 );
		}

		foreach ( $taxonomies as $taxonomy ) {
			$this->event_manager->add_callback( "manage_edit-{$taxonomy}_columns", [ $this, 'add_status_column' ] );
			$this->event_manager->add_callback( "manage_{$taxonomy}_custom_column", [ $this, 'add_taxonomy_status_data' ], 10, 3 );
		}
	}

	/**
	 * Add status column.
	 *
	 * @param array $columns Columns.
	 *
	 * @return array|string[]
	 */
	public function add_status_column( array $columns ): array {
		return array_merge( $columns, [ 'usedcss_status' => 'RUCSS status' ] );
	}

	/**
	 * Get status for post.
	 *
	 * @param string $column_key Current column key.
	 * @param int    $post_id Current Post ID.
	 *
	 * @return void
	 */
	public function add_status_data( string $column_key, int $post_id ) {
		if ( 'usedcss_status' !== $column_key ) {
			return;
		}

		$permalink = get_permalink( $post_id );

		$status = $this->used_css->get_job_status( untrailingslashit( $permalink ) );

		if ( empty( $status ) ) {
			echo 'no job';
			return;
		}

		echo $status;
	}

	/**
	 * Get status from DB for taxonomy terms.
	 *
	 * @param string $string String to be shown.
	 * @param string $column_key Columnn key.
	 * @param int    $term_id Current term ID.
	 *
	 * @return string
	 */
	public function add_taxonomy_status_data( string $string, string $column_key, int $term_id ): string {
		if ( 'usedcss_status' !== $column_key ) {
			return $string;
		}

		$permalink = get_term_link( $term_id );

		$status = $this->used_css->get_job_status( untrailingslashit( $permalink ) );

		if ( empty( $status ) ) {
			return 'no job';
		}

		return $status;
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
		if ( ! $this->settings->is_enabled() ) {
			if ( ! $this->queue->is_pending_jobs_cron_scheduled() ) {
				return;
			}

			Logger::debug( 'RUCSS: Cancel pending jobs cron job because of disabling RUCSS option.' );

			$this->queue->cancel_pending_jobs_cron();
			return;
		}

		RUCSSQueueRunner::instance()->init();

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
					'message' => __( 'Used CSS option is not enabled!', 'rocket' ),
				]
			);

			wp_safe_redirect( esc_url_raw( wp_get_referer() ) );
			rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
		}

		$this->database->truncate_used_css_table();
		rocket_clean_domain();
		rocket_dismiss_box( 'rocket_warning_plugin_modification' );

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
	 *
	 * @since 3.9
	 *
	 * @return void
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
	 * @param array $field_args    Array of field to be added to settigs page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_value( $field_args ) : array {
		return $this->settings->set_optimize_css_delivery_value( $field_args );
	}

	/**
	 * Set optimize css delivery method value
	 *
	 * @since 3.10
	 *
	 * @param array $field_args    Array of field to be added to settigs page.
	 *
	 * @return array
	 */
	public function set_optimize_css_delivery_method_value( $field_args ) : array {
		return $this->settings->set_optimize_css_delivery_method_value( $field_args );
	}

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

	public function adjust_as_concurrent_batches( int $num = 1 ) {
		return 2;
	}
}
