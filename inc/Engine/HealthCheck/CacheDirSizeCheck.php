<?php
namespace WP_Rocket\Engine\HealthCheck;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Add a weekly event to check for the cache directories sizes
 * and send a notification if it's bigger thant the defined maximum size
 *
 * @since 3.3.5
 */
class CacheDirSizeCheck implements Subscriber_Interface {
	/**
	 * Event name
	 */
	const CRON_NAME = 'rocket_cache_dir_size_check';

	/**
	 * Maximum allowed size
	 */
	const MAX_SIZE = 10737418240;

	/**
	 * ROUTE endpoint to request
	 */
	const ROUTE = 'api/wp-rocket/cache-dir-check.php';

	/**
	 * Absolute path to the minify directory
	 *
	 * @var string
	 */
	private $minify_path;

	/**
	 * Full URL to the API endpoint
	 *
	 * @var string
	 */
	private $api_endpoint;

	/**
	 * Instantiate the class
	 *
	 * @param string $minify_path Absolute path to the minify directory.
	 * @param string $rocket_url  WP Rocket website URL.
	 */
	public function __construct( $minify_path, $rocket_url ) {
		$this->minify_path  = $minify_path . get_current_blog_id();
		$this->api_endpoint = $rocket_url . self::ROUTE;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.5
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'cron_schedules'    => 'add_schedule',
			'init'              => 'schedule_cache_dir_size_check',
			self::CRON_NAME     => 'cache_dir_size_check',
			'wp_rocket_upgrade' => [ 'delete_option_after_upgrade', 11, 2 ],
		];
	}

	/**
	 * Adds the weekly interval if it doesn't already exist
	 *
	 * @since 3.3.5
	 *
	 * @param array $schedules Array of intervals.
	 * @return array
	 */
	public function add_schedule( $schedules ) {
		if ( isset( $schedules['weekly'] ) ) {
			return $schedules;
		}

		$schedules['weekly'] = [
			'interval' => 604800,
			'display'  => __( 'weekly', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Schedules the cron event if not yet scheduled.
	 *
	 * @since 3.3.5
	 *
	 * @return void
	 */
	public function schedule_cache_dir_size_check() {
		if ( ! wp_next_scheduled( self::CRON_NAME ) ) {
			wp_schedule_event( time(), 'weekly', self::CRON_NAME );
		}
	}

	/**
	 * Checks the cache dir size when the event is triggered
	 * and send a notification if the directory size is above the defined maximum size
	 *
	 * @since 3.3.5
	 *
	 * @return void
	 */
	public function cache_dir_size_check() {
		if ( 1 === (int) get_option( self::CRON_NAME ) ) {
			return;
		}

		$checks = [
			'min' => $this->minify_path,
		];

		foreach ( $checks as $type => $path ) {
			$size = $this->get_dir_size( $path );

			if ( $size > self::MAX_SIZE ) {
				$this->send_notification( $type );
			}
		}

		update_option( self::CRON_NAME, 1 );
	}

	/**
	 * Deletes the check size option when updating the plugin
	 *
	 * @since 3.3.6
	 *
	 * @param string $new_version       Latest WP Rocket version.
	 * @param string $current_version   Installed WP Rocket version.
	 */
	public function delete_option_after_upgrade( $new_version, $current_version ) {
		if ( version_compare( $current_version, $new_version, '<' ) ) {
			delete_option( self::CRON_NAME );
		}
	}

	/**
	 * Gets the size of the provided directory
	 *
	 * @since 3.3.5
	 *
	 * @param string $dir Absolute path to the directory.
	 * @return int
	 */
	private function get_dir_size( $dir ) {
		$size = 0;

		try {
			foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ) ) as $file ) {
				$size += $file->getSize();
			}

			return $size;
		} catch ( UnexpectedValueException $e ) {
			return $size;
		}
	}

	/**
	 * Sends a notification to our endpoint with the type of directory
	 *
	 * @since 3.3.5
	 *
	 * @param string $dir_type Type of directory.
	 * @return void
	 */
	private function send_notification( $dir_type ) {
		wp_safe_remote_post(
			$this->api_endpoint,
			[
				'body' => 'cache_dir_type=' . $dir_type,
			]
		);
	}
}
