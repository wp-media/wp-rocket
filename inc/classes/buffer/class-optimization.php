<?php
namespace WP_Rocket\Buffer;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handle page optimizations.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Optimization extends Abstract_Buffer {

	/**
	 * Process identifier used by the logger.
	 *
	 * @var    string
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $process_id = 'optimization process';

	/**
	 * List of the tests to do.
	 *
	 * @var    array
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 */
	protected $tests_array = [
		'query_string',
		'ssl',
		'uri',
		'rejected_cookie',
		'mandatory_cookie',
		'user_agent',
		'mobile',
		'donotcachepage',
		'wp_404',
		'search',
	];

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
		parent::__construct( $args );

		$this->log( 'OPTIMIZATION PROCESS STARTED.', [], 'info' );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** CACHE =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Do preliminary tests and maybe launch the buffer process.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 */
	public function maybe_init_process() {
		if ( ! $this->can_init_process() ) {
			$this->log_last_test_error();
			return;
		}

		ob_start( [ $this, 'maybe_process_buffer' ] );
	}

	/**
	 * Maybe optimize the page content.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return string         The buffered content.
	 */
	public function maybe_process_buffer( $buffer ) {
		if ( ! $this->is_html( $buffer ) ) {
			return $buffer;
		}

		if ( ! $this->tests->can_process_buffer( $buffer ) ) {
			$this->log_last_test_error();
			return $buffer;
		}

		/**
		 * This hook is used for:
		 * - Add width and height attributes on images
		 * - Deferred JavaScript files
		 * - DNS Prefechting
		 * - Minification HTML/CSS/JavaScript
		 * - CDN
		 * - LazyLoad
		 *
		 * @param string $buffer The page content.
		 */
		$buffer = (string) apply_filters( 'rocket_buffer', $buffer );

		$this->log( 'Page optimized.', [], 'info' );

		return $buffer;
	}
}
