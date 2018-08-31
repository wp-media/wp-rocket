<?php
namespace WP_Rocket\Admin;

use WP_Rocket\Logger;
use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class that handles few things about the logs.
 *
 * @since  3.1.4
 * @author Grégory Viguier
 */
class Logs implements Subscriber_Interface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'pre_update_option_' . WP_ROCKET_SLUG   => [ 'enable_debug', 10, 2 ],
			'admin_post_rocket_download_debug_file' => 'download_debug_file',
			'admin_post_rocket_delete_debug_file'   => 'delete_debug_file',
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** DEBUG ACTIVATION ======================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Enable or disable the debug mode when settings are saved.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  array $newvalue An array of submitted options values.
	 * @param  array $oldvalue An array of previous options values.
	 * @return array           Updated submitted options values.
	 */
	public function enable_debug( $newvalue, $oldvalue ) {
		if ( empty( $_POST ) ) {
			return $newvalue;
		}

		if ( ! empty( $newvalue['debug_enabled'] ) ) {
			Logger::enable_debug();
		} else {
			Logger::disable_debug();
		}

		unset( $newvalue['debug_enabled'] );

		return $newvalue;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** ADMIN POST CALLBACKS ==================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Download the log file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function download_debug_file() {
		if ( ! $this->verify_nonce( 'download_debug_file' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! $this->current_user_can() ) {
			$this->redirect();
		}

		$contents = Logger::get_log_file_contents();

		if ( is_wp_error( $contents ) ) {
			add_settings_error( 'general', $contents->get_error_code(), $contents->get_error_message(), 'error' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );

			$this->redirect( add_query_arg( 'settings-updated', 1, wp_get_referer() ) );
		}

		$file_name = Logger::get_log_file_path();
		$file_name = basename( $file_name, '.log' ) . Logger::get_log_file_extension();

		nocache_headers();
		@header( 'Content-Type: text/x-log' );
		@header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		@header( 'Content-Transfer-Encoding: binary' );
		@header( 'Content-Length: ' . strlen( $contents ) );
		@header( 'Connection: close' );
		echo $contents;
		exit();
	}

	/**
	 * Delete the log file.
	 *
	 * @since  3.1.4
	 * @access public
	 * @author Grégory Viguier
	 */
	public function delete_debug_file() {
		if ( ! $this->verify_nonce( 'delete_debug_file' ) ) {
			wp_nonce_ays( '' );
		}

		if ( ! $this->current_user_can() ) {
			$this->redirect();
		}

		if ( ! Logger::delete_log_file() ) {
			add_settings_error( 'general', 'debug_file_not_deleted', __( 'The debug file could not be deleted.', 'rocket' ), 'error' );
			set_transient( 'settings_errors', get_settings_errors(), 30 );

			$this->redirect( add_query_arg( 'settings-updated', 1, wp_get_referer() ) );
		}

		// Done.
		$this->redirect();
	}


	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Verify the nonce sent in $_GET['_wpnonce'].
	 *
	 * @since  3.1.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  string $nonce_name The nonce name.
	 * @return bool
	 */
	protected function verify_nonce( $nonce_name ) {
		return isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], $nonce_name );
	}

	/**
	 * Tell if the current user can operate.
	 *
	 * @since  3.1.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	protected function current_user_can() {
		/** This filter is documented in inc/admin-bar.php */
		return current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) );
	}

	/**
	 * Redirect the user.
	 *
	 * @since  3.1.4
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param string $redirect URL to redirect the user to.
	 */
	protected function redirect( $redirect = null ) {
		if ( empty( $redirect ) ) {
			$redirect = wp_get_referer();
		}

		wp_safe_redirect( esc_url_raw( $redirect ) );
		die();
	}
}
