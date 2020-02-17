<?php
namespace WP_Rocket\Preload;

defined( 'ABSPATH' ) || exit;

/**
 * Extends the background process class for the preload background process.
 *
 * @since 3.2
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class Full_Process extends Process {
	/**
	 * Specific action identifier for the current preload type.
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var string
	 */
	protected $action = 'preload';

	/**
	 * Preload the URL provided by $item.
	 *
	 * @since  3.2
	 * @since  3.5 $item can be an array.
	 * @author Remy Perona
	 *
	 * @param  array|string $item {
	 *     The item to preload: an array containing the following values.
	 *     A string is allowed for backward compatibility (for the URL).
	 *
	 *     @type string $url    The URL to preload.
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request. Optional.
	 * }
	 * @return bool False.
	 */
	protected function task( $item ) {
		$count = get_transient( 'rocket_preload_running' );
		set_transient( 'rocket_preload_running', $count + 1 );

		return $this->maybe_preload( $item );
	}

	/**
	 * Updates transients on complete
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function complete() {
		set_transient( 'rocket_preload_complete', get_transient( 'rocket_preload_running' ) );
		set_transient( 'rocket_preload_complete_time', date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) );
		delete_transient( 'rocket_preload_running' );
		parent::complete();
	}

	/**
	 * Checks if a process is already running.
	 * This allows the method to be public.
	 *
	 * @since  3.2.1.1
	 * @access public
	 * @author Remy Perona
	 * @see WP_Background_Process::is_process_running()
	 *
	 * @return boolean
	 */
	public function is_process_running() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::is_process_running();
	}
}
