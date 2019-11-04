<?php
namespace WP_Rocket\Buffer;

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
	 * Tests instance
	 *
	 * @var Tests
	 */
	protected $tests;

	/**
	 * Constructor.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param Tests $tests Tests instance.
	 */
	public function __construct( Tests $tests ) {
		parent::__construct( $tests );

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
		if ( ! $this->tests->can_init_process() ) {
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
		/**
		 * Triggered before WP Rocket starts the optimization process.
		 *
		 * @since  3.4.2
		 * @author Soponar Cristina
		 *
		 * @param string $buffer HTML content.
		 */
		do_action( 'rocket_before_maybe_process_buffer', $buffer );

		if ( ! $this->is_html( $buffer ) ) {
			return $buffer;
		}

		if ( ! $this->tests->can_process_buffer( $buffer ) ) {
			$this->log_last_test_error();
			return $buffer;
		}

		/**
		 * This hook is used for:
		 * - Async CSS files
		 * - Defer JavaScript files
		 * - Minify/Combine HTML/CSS/JavaScript
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
