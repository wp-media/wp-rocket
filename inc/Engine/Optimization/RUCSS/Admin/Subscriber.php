<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
use WP_Rocket\Engine\Common\Queue\RUCSSQueueRunner;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
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
	 * Queue instance
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Instantiate the class
	 *
	 * @param Settings $settings Settings instance.
	 * @param Database $database Database instance.
	 * @param UsedCSS  $used_css UsedCSS instance.
	 * @param Queue    $queue    Queue instance.
	 */
	public function __construct( Settings $settings, Database $database, UsedCSS $used_css, Queue $queue ) {
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
	public static function get_subscribed_events(): array {
		$slug = rocket_get_constant( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

		return [
			'update_option_' . $slug                  => [
				[ 'clean_used_css_and_cache', 9, 2 ],
				[ 'maybe_set_processing_transient', 50, 2 ],
				[ 'maybe_unlock_preload', 9, 2 ],
				[ 'maybe_delete_transient', 10, 2 ],
			],
			'switch_theme'                            => 'truncate_used_css',
			'permalink_structure_changed'             => 'truncate_used_css',
			'rocket_domain_options_changed'           => 'truncate_used_css',
			'wp_trash_post'                           => 'delete_used_css_on_update_or_delete',
			'delete_post'                             => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                        => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'                 => 'delete_used_css_on_update_or_delete',
			'edit_term'                               => 'delete_term_used_css',
			'pre_delete_term'                         => 'delete_term_used_css',
			'admin_notices'                           => [
				[ 'display_no_table_notice' ],
				[ 'notice_write_permissions' ],
			],
			'rocket_before_add_field_to_settings'     => [
				[ 'set_optimize_css_delivery_value', 10, 1 ],
				[ 'set_optimize_css_delivery_method_value', 10, 1 ],
			],
			'wp_rocket_upgrade'                       => [
				[ 'set_option_on_update', 14, 2 ],
				[ 'update_safelist_items', 15, 2 ],
				[ 'delete_used_css', 16, 2 ],
				[ 'cancel_pending_jobs_as', 16, 2 ],
				[ 'drop_resources_table', 18, 2 ],
			],
			'wp_ajax_rocket_spawn_cron'               => 'spawn_cron',
			'rocket_deactivation'                     => 'cancel_queues',
			'admin_head-tools_page_action-scheduler'  => 'delete_as_tables_transient_on_tools_page',
			'pre_get_rocket_option_remove_unused_css' => 'disable_russ_on_wrong_license',
			'rocket_before_rollback'                  => 'cancel_queues',
			'rocket_saas_clean_all'                   => [ 'truncate', 11 ],
			'rocket_saas_clean_url'                   => [ 'clean_url', 11 ],
		];
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

		if ( ! $this->is_deletion_enabled() ) {
			return;
		}

		$url = get_permalink( $post_id );

		if ( false === $url ) {
			return;
		}

		$this->used_css->delete_used_css( untrailingslashit( $url ) );
	}

	/**
	 * Maybe unlock all locked preload urls.
	 *
	 * @param array $old_value An array of submitted values for the settings.
	 * @param array $value     An array of previous values for the settings.
	 *
	 * @return void
	 */
	public function maybe_unlock_preload( $old_value, $value ) {
		if ( ! isset( $value['remove_unused_css'], $old_value['remove_unused_css'] ) ) {
			return;
		}

		if ( $value['remove_unused_css'] === $old_value['remove_unused_css'] ) {
			return;
		}

		if ( $value['remove_unused_css'] ) {
			return;
		}

		do_action( 'rocket_preload_unlock_all_urls' );
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

		if ( ! $this->is_deletion_enabled() ) {
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

		if ( ! $this->is_deletion_enabled() ) {
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
		$this->used_css->delete_all_used_css();

		if ( 0 < $this->used_css->get_not_completed_count() ) {
			$this->database->remove_all_completed_rows();
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
	 * Deletes rows when triggering clean from admin
	 *
	 * @param array $clean An array containing the status and message.
	 *
	 * @return array
	 */
	public function truncate( $clean ) {
		if ( ! $this->settings->is_enabled() ) {
			return $clean;
		}

		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return [
				'status' => 'die',
			];
		}

		$this->delete_used_css_rows();

		return [
			'status'  => 'success',
			'message' => sprintf(
				// translators: %1$s = plugin name.
				__( '%1$s: Used CSS cache cleared!', 'rocket' ),
				'<strong>WP Rocket</strong>'
			),
		];
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
	 * Clean UsedCSS for the current URL.
	 *
	 * @return void
	 */
	public function clean_url() {
		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			wp_nonce_ays( '' );
		}

		$url = wp_get_referer();

		if ( 0 !== strpos( $url, 'http' ) ) {
			$parse_url = get_rocket_parse_url( untrailingslashit( home_url() ) );
			$url       = $parse_url['scheme'] . '://' . $parse_url['host'] . $url;
		}

		$this->used_css->clear_url_usedcss( $url );
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
	 * Cancel pending jobs actions in Action Scheduler on update to 3.11.3
	 *
	 * @since 3.11.3
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function cancel_pending_jobs_as( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.11.3', '>=' ) ) {
			return;
		}

		try {
			$this->queue->cancel_pending_jobs_cron();
		} catch ( \InvalidArgumentException $e ) {
			// nothing to do.
			return;
		}
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
			'rocket_saas_processing',
			time() + 90,
			1.5 * MINUTE_IN_SECONDS
		);

		rocket_renew_box( 'saas_success_notice' );
	}

	/**
	 * Sends a request to run cron when switching to RUCSS completed notice
	 *
	 * @since 3.11
	 *
	 * @return void
	 */
	public function spawn_cron() {
		if ( rocket_get_constant( 'DISABLE_WP_CRON', false ) ) {
			return;// Bailout and don't fire the CRON.
		}

		check_ajax_referer( 'rocket-ajax', 'nonce' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_send_json_error();
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
		// Will unhook check for dispatching an async request without RUCSS process running.
		\ActionScheduler_QueueRunner::instance()->unhook_dispatch_async_request();

		// Will unhook check for dispatching an async request when RUCSS process is already running.
		RUCSSQueueRunner::instance()->unhook_dispatch_async_request();

		$this->queue->cancel_pending_jobs_cron();

		if ( ! wp_next_scheduled( 'rocket_saas_clean_rows_time_event' ) ) {
			return;
		}

		wp_clear_scheduled_hook( 'rocket_saas_clean_rows_time_event' );
	}

	/**
	 * Delete the transient for Action scheduler once admin visits the AS tools page.
	 *
	 * @return void
	 */
	public function delete_as_tables_transient_on_tools_page() {
		delete_transient( 'rocket_rucss_as_tables_count' );
	}

	/**
	 * Deletes the used CSS on update to 3.11.4 for new storage method
	 *
	 * @since 3.11.4
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function delete_used_css( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.11.4', '>=' ) ) {
			return;
		}

		$this->database->truncate_used_css_table();
	}

	/**
	 * Disable RUCSS on wrong license.
	 *
	 * @return null|false
	 */
	public function disable_russ_on_wrong_license() {
		if ( false !== (bool) get_option( 'wp_rocket_no_licence' ) ) {
			return false;
		}

		return null;
	}

	/**
	 * Remove the resources table & version stored in options table on update to 3.12
	 *
	 * @since 3.12
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function drop_resources_table( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.12', '>=' ) ) {
			return;
		}

		$this->database->drop_resources_table();
	}

	/**
	 * Displays a notice if the used CSS folder is not writable
	 *
	 * @since 3.11.4
	 *
	 * @return void
	 */
	public function notice_write_permissions() {
		$this->used_css->notice_write_permissions();
	}

	/**
	 * Display a notice on table missing.
	 *
	 * @return void
	 */
	public function display_no_table_notice() {
		$this->settings->display_no_table_notice();
	}

	/**
	 * Maybe delete transient.
	 *
	 * @param mixed $old_value Option old value.
	 * @param mixed $value     Option new value.
	 *
	 * @return void
	 */
	public function maybe_delete_transient( $old_value, $value ) {
		if ( ! isset( $old_value['remove_unused_css'], $value['remove_unused_css'] ) ) {
			return;
		}

		if ( 1 === (int) $value['remove_unused_css'] ) {
			return;
		}

		if ( $old_value['remove_unused_css'] === $value['remove_unused_css'] ) {
			return;
		}

		delete_transient( 'wp_rocket_no_licence' );
	}

	/**
	 * Checks if the SaaS deletion is enabled.
	 *
	 * @return bool
	 */
	protected function is_deletion_enabled(): bool {
		/**
		 * Filters the enable SaaS deletion value
		 *
		 * @param bool $delete_saas_jobs True to enable deletion, false otherwise.
		 */
		return (bool) rocket_apply_filter_and_deprecated(
			'rocket_saas_deletion_enabled',
			[ true ],
			'3.16',
			'rocket_rucss_deletion_enabled'
		);
	}
}
