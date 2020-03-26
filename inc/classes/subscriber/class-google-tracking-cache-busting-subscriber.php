<?php
namespace WP_Rocket\Subscriber;

use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Busting\Busting_Factory;
use WP_Rocket\Admin\Options_Data as Options;
use WP_Rocket\Logger\Logger;

/**
 * Event subscriber for Google tracking cache busting
 *
 * @since 3.1
 * @author Remy Perona
 */
class Google_Tracking_Cache_Busting_Subscriber implements Subscriber_Interface {
	/**
	 * Instance of the Busting Factory class
	 *
	 * @var Busting_Factory
	 */
	private $busting_factory;

	/**
	 * Instance of the Option_Data class
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Busting_Factory $busting_factory Instance of the Busting Factory class.
	 * @param Options         $options Instance of the Option_Data class.
	 */
	public function __construct( Busting_Factory $busting_factory, Options $options ) {
		$this->busting_factory = $busting_factory;
		$this->options         = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'cron_schedules'                      => 'add_schedule',
			'init'                                => 'schedule_tracking_cache_update',
			'rocket_google_tracking_cache_update' => 'update_tracking_cache',
			'rocket_purge_cache'                  => 'delete_tracking_cache',
			'rocket_buffer'                       => 'cache_busting_google_tracking',
			'admin_notices'                       => 'busting_dir_not_writable_admin_notice',
		];

		return $events;
	}

	/**
	 * Processes the cache busting on the HTML content
	 *
	 * Google Analytics replacement is performed first, and if no replacement occured, Google Tag Manager replacement is performed.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function cache_busting_google_tracking( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		$processor = $this->busting_factory->type( 'ga' );
		$html      = $processor->replace_url( $html );

		$processor = $this->busting_factory->type( 'gtm' );
		$html      = $processor->replace_url( $html );

		return $html;
	}

	/**
	 * Schedules the auto-update of Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function schedule_tracking_cache_update() {
		if ( ! $this->is_busting_active() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'rocket_google_tracking_cache_update' ) ) {
			wp_schedule_event( time(), 'weekly', 'rocket_google_tracking_cache_update' );
		}
	}

	/**
	 * Updates Google Analytics cache busting file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function update_tracking_cache() {
		if ( ! $this->is_busting_active() ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->refresh_save( $processor->get_url() );
	}

	/**
	 * Adds weekly interval to cron schedules
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Array $schedules An array of intervals used by cron jobs.
	 * @return Array
	 */
	public function add_schedule( $schedules ) {
		if ( ! $this->is_busting_active() ) {
			return $schedules;
		}

		$schedules['weekly'] = [
			'interval' => 604800,
			'display'  => __( 'weekly', 'rocket' ),
		];

		return $schedules;
	}

	/**
	 * Deletes the GA busting file.
	 *
	 * @since  3.1
	 * @since  3.6 Argument replacement.
	 * @author Remy Perona
	 *
	 * @param  string $_type Type of cache clearance: 'all', 'post', 'term', 'user', 'url'.
	 * @return bool
	 */
	public function delete_tracking_cache( $_type ) {
		if ( 'all' !== $_type || ! $this->is_busting_active() ) {
			return false;
		}

		$processor = $this->busting_factory->type( 'ga' );

		return $processor->delete();
	}

	/**
	 * Display an admin notice if the cache folder is not writable.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 */
	public function busting_dir_not_writable_admin_notice() {
		if ( ! $this->is_busting_active() || ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$dir_paths = [
			$this->busting_factory->type( 'fbsdk' )->get_busting_dir_path(),
			$this->busting_factory->type( 'fbpix' )->get_busting_dir_path(),
		];

		$dir_paths  = array_unique( $dir_paths );
		$filesystem = rocket_direct_filesystem();

		foreach ( $dir_paths as $i => $dir_path ) {
			if ( ! $filesystem->exists( $dir_path ) ) {
				rocket_mkdir_p( $dir_path );
			}
			if ( $filesystem->exists( $dir_path ) && $filesystem->is_writable( $dir_path ) ) {
				unset( $dir_paths[ $i ] );
			} else {
				$dir_paths[ $i ] = '<code>' . esc_html( trim( str_replace( ABSPATH, '', $dir_path ), '/' ) ) . '</code>';
			}
		}

		if ( ! $dir_paths ) {
			return;
		}

		$message  = '<strong>' . __( 'WP Rocket: ', 'rocket' ) . '</strong>';
		$message .= sprintf(
			/* translators: %s is a list of folder paths. */
			_n( 'The folder %s used to cache Google tracking scripts could not be created or is missing writing permissions.', 'The folders %s used to cache Google tracking scripts could not be created or are missing writing permissions.', count( $dir_paths ), 'rocket' ),
			wp_sprintf_l( '%l', $dir_paths )
		);
		$message .= '<br>' . sprintf(
			/* translators: This is a doc title! %1$s = opening link; %2$s = closing link */
			__( 'Troubleshoot: %1$sHow to make system files writeable%2$s', 'rocket' ),
			/* translators: Documentation exists in EN, DE, FR, ES, IT; use loaclised URL if applicable */
			'<a href="' . __( 'https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket', 'rocket' ) . '" target="_blank">',
			'</a>'
		);

		rocket_notice_html(
			[
				'status'      => 'error',
				'dismissible' => '',
				'message'     => $message,
			]
		);
	}

	/**
	 * Checks if the cache busting should happen
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( defined( 'DONOTROCKETOPTIMIZE' ) && DONOTROCKETOPTIMIZE ) {
			return false;
		}

		return $this->is_busting_active();
	}

	/**
	 * Tell if the cache busting option is active.
	 *
	 * @since  3.6
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	private function is_busting_active() {
		return (bool) $this->options->get( 'google_analytics_cache', 0 );
	}
}
