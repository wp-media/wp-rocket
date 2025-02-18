<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\Buffer;

use WP_Rocket\Buffer\Abstract_Buffer;
use WP_Rocket\Buffer\Tests;

/**
 * Handle page optimizations.
 *
 * @since 3.3
 */
class Optimization extends Abstract_Buffer {

	/**
	 * Process identifier used by the logger.
	 *
	 * @var    string
	 * @since  3.3
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

		if ( ! $this->is_feed_uri() && ! $this->is_html( $buffer ) ) {
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
		$filtered_buffer = (string) apply_filters( 'rocket_buffer', $buffer );

		if ( empty( $filtered_buffer ) ) {
			$this->log_last_test_error();
			$this->log( 'Empty buffer.', [], 'error' );
			return $buffer;
		}

		$this->log( 'Page optimized.', [], 'info' );

		/**
		 * Fires after processing the buffer
		 *
		 * @since 3.12
		 */
		do_action( 'rocket_after_process_buffer' );

		return $filtered_buffer;
	}

	/**
	 * Tell if the current url is a feed.
	 *
	 * @return bool
	 */
	public function is_feed_uri() {
		global $wp_rewrite, $wp;
		$feed_uri = '/(?:.+/)?' . $wp_rewrite->feed_base . '(?:/(?:.+/?)?)?$';
		return (bool) preg_match( '#^(' . $feed_uri . ')$#i', '/' . $wp->request );
	}
}
