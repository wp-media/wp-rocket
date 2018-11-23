<?php
namespace WP_Rocket\Buffer;

use WP_Rocket\Logger\Logger;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handle page cache and optimizations.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
abstract class Abstract_Buffer {

	/**
	 * Process identifier used by the logger.
	 *
	 * @var    string
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $process_id;

	/**
	 * List of the tests to do.
	 *
	 * @var    array
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $tests_array;

	/**
	 * Instance of the Tests class.
	 *
	 * @var    \WP_Rocket\Buffer\Tests
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $tests;

	/**
	 * Constructor.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $args  {
	 *     An array of arguments.
	 *
	 *     @type string $config_dir_path Path to the directory containing the config files.
	 * }
	 */
	public function __construct( array $args ) {
		$this->tests = new Tests(
			[
				'config_dir_path' => $args['config_dir_path'],
				'tests'           => $this->tests_array,
			]
		);
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PROCESS ================================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if the process should be initiated.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	protected function can_init_process() {
		return $this->tests->can_init_process();
	}

	/**
	 * Maybe launch the process.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 */
	abstract public function maybe_init_process();

	/**
	 * Process the page buffer.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return string         The buffered content
	 */
	abstract public function maybe_process_buffer( $buffer );

	/** ----------------------------------------------------------------------------------------- */
	/** LOG ===================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Log the last test "error".
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected function log_last_test_error() {
		$error = $this->tests->get_last_error();

		$this->log( $error['message'], $error['data'] );
	}

	/**
	 * Log events.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param string $message A message to log.
	 * @param array  $data    Related data.
	 * @param string $type    Event type to log. Possible values are 'info', 'error', and 'debug' (default).
	 */
	protected function log( $message, $data = [], $type = 'debug' ) {
		$data = array_merge(
			[
				$this->get_process_id(),
				'request_uri' => $this->tests->get_raw_request_uri(),
			],
			$data
		);

		if ( isset( $data['cookies'] ) ) {
			$data['cookies'] = Logger::remove_auth_cookies( $data['cookies'] );
		}

		switch ( $type ) {
			case 'info':
				Logger::info( $message, $data );
				break;
			case 'error':
				Logger::error( $message, $data );
				break;
			default:
				Logger::debug( $message, $data );
		}
	}

	/**
	 * Get the process identifier.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_process_id() {
		return $this->process_id . ' - Thread #' . Logger::get_thread_id();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** VARIOUS TOOLS =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Declares and sets value of constant preventing Optimizations.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param bool $value True or false. Default is true.
	 */
	final public function define_donotoptimize( $value = true ) {
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', (bool) $value );
		}
	}

	/**
	 * Tell if the page content is HTML.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return bool
	 */
	final protected function is_html( $buffer ) {
		return preg_match( '/(<\/html>)/i', $buffer );
	}
}
