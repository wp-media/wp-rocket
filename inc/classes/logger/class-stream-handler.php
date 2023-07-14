<?php
namespace WP_Rocket\Logger;

use UnexpectedValueException;
use Monolog\Handler\StreamHandler;

/**
 * Class used to log records into a local file.
 *
 * @since 3.2
 */
class Stream_Handler extends StreamHandler {

	/**
	 * Tell if the .htaccess file exists.
	 *
	 * @var bool
	 *
	 * @since 3.2
	 */
	private $htaccess_exists;

	/**
	 * Tell if there is an error.
	 *
	 * @var bool
	 *
	 * @since 3.2
	 */
	private $has_error;

	/**
	 * Contains an error message.
	 *
	 * @var string
	 *
	 * @since 3.2
	 */
	private $error_message;

	/**
	 * Writes the record down to the log of the implementing handler.
	 *
	 * @since 3.2
	 *
	 * @param array $record Log contents.
	 *
	 * @return void
	 */
	protected function write( array $record ): void {
		parent::write( $record );
		$this->create_htaccess_file();
	}

	/**
	 * Create a .htaccess file in the log folder, to prevent direct access and directory listing.
	 *
	 * @since 3.2
	 *
	 * @throws \UnexpectedValueException When the .htaccess file could not be created.
	 *
	 * @return bool True if the file exists or has been created. False on failure.
	 */
	public function create_htaccess_file() {
		if ( $this->htaccess_exists ) {
			return true;
		}

		if ( $this->has_error ) {
			return false;
		}

		$dir = $this->get_dir_from_stream( $this->url );

		if ( ! $dir || ! is_dir( $dir ) ) {
			$this->has_error = true;
			return false;
		}

		$file_path = $dir . '/.htaccess';

		if ( file_exists( $file_path ) ) {
			$this->htaccess_exists = true;
			return true;
		}

		$this->error_message = null;

		set_error_handler( [ $this, 'custom_error_handler' ] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler

		$file_resource = fopen( $file_path, 'a' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen

		restore_error_handler();

		if ( ! is_resource( $file_resource ) ) {
			$this->has_error = true;
			throw new UnexpectedValueException( sprintf( 'The file "%s" could not be opened: ' . $this->error_message, $file_path ) );
		}

		$new_content = "<Files ~ \"\.log$\">\nOrder allow,deny\nDeny from all\n</Files>\nOptions -Indexes";

		fwrite( $file_resource, $new_content );  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
		fclose( $file_resource );  // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		@chmod( $file_path, 0644 );

		$this->htaccess_exists = true;

		return true;
	}

	/**
	 * Temporary error handler that "cleans" the error messages.
	 *
	 * @since 3.2
	 *
	 * @see parent::customErrorHandler()
	 *
	 * @param int    $code Error code.
	 * @param string $msg  Error message.
	 */
	private function custom_error_handler( int $code, string $msg ) {
		$this->error_message = preg_replace( '{^(fopen|mkdir)\(.*?\): }', '', $msg );
	}

	/**
	 * A dirname() that also works for streams, by removing the protocol.
	 *
	 * @since  3.2
	 *
	 * @see parent::getDirFromStream()
	 *
	 * @param  string $stream Path to a file.
	 *
	 * @return null|string
	 */
	private function get_dir_from_stream( string $stream ) {
		$pos = strpos( $stream, '://' );

		if ( false === $pos ) {
			return dirname( $stream );
		}

		if ( 'file://' === substr( $stream, 0, 7 ) ) {
			return dirname( substr( $stream, 7 ) );
		}
	}
}
