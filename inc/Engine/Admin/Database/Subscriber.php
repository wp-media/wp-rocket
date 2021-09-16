<?php
namespace WP_Rocket\Engine\Admin\Database;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the database optimization
 */
class Subscriber implements Subscriber_Interface {

	/**
	 * Optimization process instance.
	 *
	 * @since  3.4
	 *
	 * @var Optimization
	 */
	private $optimize;

	/**
	 * WP Rocket Options instance.
	 *
	 * @since  3.4
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Optimization $optimize Optimize instance.
	 * @param Options_Data $options  WP Rocket options.
	 */
	public function __construct( Optimization $optimize, Options_Data $options ) {
		$this->optimize = $optimize;
		$this->options  = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'cron_schedules'                          => 'add_cron_schedule',
			'init'                                    => 'database_optimization_scheduled',
			'rocket_database_optimization_time_event' => 'cron_optimize',
			'pre_update_option_' . WP_ROCKET_SLUG     => 'save_optimize',
			'admin_notices'                           => [
				[ 'notice_process_running' ],
				[ 'notice_process_complete' ],
			],
		];
	}

	/**
	 * Add a new interval for the cron job.
	 * This adds a weekly/monthly interval for database optimization.
	 *
	 * @since 3.4
	 *
	 * @param  array $schedules An array of intervals used by cron jobs.
	 * @return array            Updated array of intervals.
	 */
	public function add_cron_schedule( $schedules ) {
		if ( ! $this->options->get( 'schedule_automatic_cleanup', false ) ) {
			return $schedules;
		}

		switch ( $this->options->get( 'automatic_cleanup_frequency', 'weekly' ) ) {
			case 'weekly':
				$schedules['weekly'] = [
					'interval' => 604800,
					'display'  => __( 'weekly', 'rocket' ),
				];
				break;
			case 'monthly':
				$schedules['monthly'] = [
					'interval' => 2592000,
					'display'  => __( 'monthly', 'rocket' ),
				];
				break;
		}

		return $schedules;
	}

	/**
	 * Plans database optimization cron
	 * If the task is not programmed, it is automatically triggered
	 *
	 * @since 2.8
	 *
	 * @see process_handler()
	 */
	public function database_optimization_scheduled() {
		if ( ! $this->options->get( 'schedule_automatic_cleanup', false ) ) {
			return;
		}

		if ( ! wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
			wp_schedule_event( time(), $this->options->get( 'automatic_cleanup_frequency', 'weekly' ), 'rocket_database_optimization_time_event' );
		}
	}

	/**
	 * Database Optimization cron callback
	 *
	 * @since 3.0.4
	 */
	public function cron_optimize() {
		$items = array_filter( array_keys( $this->optimize->get_options() ), [ $this->options, 'get' ] );

		if ( empty( $items ) ) {
			return;
		}

		$this->optimize->process_handler( $items );
	}

	/**
	 * Launches the database optimization when the settings are saved with optimize button
	 *
	 * @since 2.8
	 *
	 * @see process_handler()
	 *
	 * @param array $value The new, unserialized option value.
	 * @return array
	 */
	public function save_optimize( $value ) {
		if ( empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return $value;
		}

		if ( empty( $value ) || ! isset( $value['submit_optimize'] ) ) {
			return $value;
		}

		unset( $value['submit_optimize'] );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return $value;
		}

		$items      = [];
		$db_options = $this->optimize->get_options();

		foreach ( $value as $key => $option_value ) {
			if ( isset( $db_options[ $key ] ) && 1 === $option_value ) {
				$items[] = $key;
			}
		}

		if ( empty( $items ) ) {
			return $value;
		}

		$this->optimize->process_handler( $items );

		return $value;
	}

	/**
	 * This notice is displayed after launching the database optimization process
	 *
	 * @since 2.11
	 */
	public function notice_process_running() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$notice = get_transient( 'rocket_database_optimization_process' );

		if ( ! $notice ) {
			return;
		}

		\rocket_notice_html(
			[
				'status'  => 'info',
				'message' => esc_html__( 'Database optimization process is running', 'rocket' ),
			]
		);
	}

	/**
	 * This notice is displayed when the database optimization process is complete
	 *
	 * @since 2.11
	 */
	public function notice_process_complete() {
		$screen = get_current_screen();

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( 'settings_page_wprocket' !== $screen->id ) {
			return;
		}

		$optimized = get_transient( 'rocket_database_optimization_process_complete' );

		if ( false === $optimized ) {
			return;
		}

		$db_options = $this->optimize->get_options();
		delete_transient( 'rocket_database_optimization_process_complete' );

		$message = esc_html__( 'Database optimization process is complete. Everything was already optimized!', 'rocket' );

		if ( ! empty( $optimized ) ) {
			$message = esc_html__( 'Database optimization process is complete. List of optimized items below:', 'rocket' );
		}

		if ( ! empty( $optimized ) ) {
			$message .= '<ul>';
			foreach ( $optimized as $key => $number ) {
				$message .= '<li>' .
					/* translators: %1$d = number of items optimized, %2$s = type of optimization */
					sprintf( esc_html__( '%1$d %2$s optimized.', 'rocket' ), $number, $db_options[ $key ] )
				. '</li>';
			}
			$message .= '</ul>';
		}

		\rocket_notice_html(
			[
				'message' => $message,
			]
		);
	}
}
