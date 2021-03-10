<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

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
		return [
			'rocket_first_install_options'     => 'add_options_first_time',
			'wp_rocket_upgrade'                => [
				[ 'set_option_on_update', 13, 2 ],
			],
			'switch_theme'                     => 'truncate_used_css',
			'rocket_rucss_file_changed'        => 'truncate_used_css',
			'pre_post_update'                  => 'delete_used_css_on_update_or_delete',
			'wp_trash_post'                    => 'delete_used_css_on_update_or_delete',
			'delete_post'                      => 'delete_used_css_on_update_or_delete',
			'clean_post_cache'                 => 'delete_used_css_on_update_or_delete',
			'wp_update_comment_count'          => 'delete_used_css_on_update_or_delete',
			'init'                             => 'clean_used_css_scheduled',
			'rocket_clean_used_css_time_event' => 'cron_clean_used_css',
		];
	}

	/**
	 * Used CSS cron callback for deleting old used css.
	 *
	 * @return void
	 */
	public function cron_clean_used_css() {
		$last_accessed = strtotime( current_time( 'mysql' ) . ' - 1 month' );
		$this->used_css->delete_old_used_css();
	}

	/**
	 * Schedules cron for used CSS.
	 *
	 * @return void
	 */
	public function clean_used_css_scheduled() {
		if ( ! wp_next_scheduled( 'rocket_clean_used_css_time_event' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_clean_used_css_time_event' );
		}
	}

	/**
	 * Delete used_css on Update Post or Delete post.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return void
	 */
	public function delete_used_css_on_update_or_delete( $post_id ) {
		$url = untrailingslashit( get_permalink( $post_id ) );

		$this->used_css->delete_used_css( $url );
	}

	/**
	 * Truncate RUCSS used_css DB table.
	 *
	 * @return void
	 */
	public function truncate_used_css() {
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
}
