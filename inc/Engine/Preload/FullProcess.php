<?php

namespace WP_Rocket\Engine\Preload;

defined( 'ABSPATH' ) || exit;

/**
 * Extends the background process class for the preload background process.
 *
 * @since 3.2
 * @author Remy Perona
 *
 * @see WP_Background_Process
 */
class FullProcess extends AbstractProcess {
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
	 *     @type bool   $mobile True when we want to send a "mobile" user agent with the request.
	 *     @type string $source An identifier related to the source of the preload.
	 * }
	 * @return bool False.
	 */
	protected function task( $item ) {
		$result = $this->maybe_preload( $item );

		if ( $result && ! empty( $item['source'] ) && ( ! is_array( $item ) || empty( $item['mobile'] ) ) ) {
			// Count only successful non mobile items.
			$transient_name = sprintf( 'rocket_%s_preload_running', $item['source'] );
			$preload_count  = get_transient( $transient_name );
			set_transient( $transient_name, $preload_count + 1 );
		}

		return false;
	}

	/**
	 * Updates transients on complete
	 *
	 * @since 3.2
	 * @author Remy Perona
	 */
	public function complete() {
		$homepage_count = get_transient( 'rocket_homepage_preload_running' );
		$sitemap_count  = get_transient( 'rocket_sitemap_preload_running' );

		set_transient( 'rocket_preload_complete', $homepage_count + $sitemap_count );
		set_transient( 'rocket_preload_complete_time', date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) ) );
		delete_transient( 'rocket_homepage_preload_running' );
		delete_transient( 'rocket_sitemap_preload_running' );

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
