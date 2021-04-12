<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
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
			'rocket_input_sanitize'              => [ 'sanitize_options', 10, 2 ],
			'pre_update_option_' . $slug         => [ 'clean_used_css_and_cache', 10, 2 ],
			'wp_rocket_upgrade'                  => [
				[ 'set_option_on_update', 13, 2 ],
			],
			'switch_theme'                       => 'truncate_used_css',
			'rocket_rucss_file_changed'          => 'truncate_used_css',
			'wp_trash_post'                      => 'delete_used_css_on_update_or_delete',
			'delete_post'                        => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                   => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'            => 'delete_used_css_on_update_or_delete',
			'init'                               => 'schedule_clean_not_commonly_used_rows',
			'rocket_rucss_clean_rows_time_event' => 'cron_clean_rows',
		];
	}

	/**
	 * Cron callback for deleting old rows in both table databases.
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

		$url = untrailingslashit( get_permalink( $post_id ) );

		$this->used_css->delete_used_css( $url );
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
	 * Sets the RUCSS options to defaults when updating to 3.9
	 *
	 * @since 3.9
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 *
	 * @return void
	 */
	public function set_option_on_update( $new_version, $old_version ) {
		$this->settings->set_option_on_update( $old_version );
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
	 * @param array $value     An array of previous values for the settings.
	 * @param array $old_value An array of submitted values for the settings.
	 *
	 * @return array settings with settings.
	 */
	public function clean_used_css_and_cache( $value, $old_value ) : array {
		if ( ! current_user_can( 'rocket_manage_options' )
			||
			! $this->settings->is_enabled()
		) {
			return $value;
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

		return $value;
	}
}
