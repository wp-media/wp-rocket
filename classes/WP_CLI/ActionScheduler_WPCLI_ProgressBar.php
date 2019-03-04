<?php

/**
 * WP_CLI progress bar for the Action Scheduler by Prospress.
 */
class ActionScheduler_WPCLI_ProgressBar {

	/** @var integer */
	protected $total_ticks;

	/** @var integer */
	protected $free_ticks;

	/** @var integer */
	protected $count;

	/** @var integer */
	protected $interval;

	/** @var string */
	protected $message;

	/** @var \cli\progress\Bar */
	protected $progress_bar;

	/**
	 * ActionScheduler_WPCLI_ProgressBar constructor.
	 *
	 * @param string  $message    Text to display before the progress bar.
	 * @param integer $count      Total number of ticks to be performed.
	 * @param integer $free_ticks Optional. The number of ticks before freeing memory. Default 50.
	 * @param integer $interval   Optional. The interval in milliseconds between updates. Default 100.
 	 *
	 * @throws Exception When this is not run within WP CLI
	 */
	public function __construct( $message, $count, $free_ticks = 50, $interval = 100 ) {
		if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			/* translators: %s php class name */
			throw new Exception( sprintf( __( 'The %s class can only be run within WP CLI.', 'action-scheduler' ), __CLASS__ ) );
		}

		$total_ticks      = 0;
		$this->message    = $message;
		$this->count      = $count;
		$this->free_ticks = $free_ticks;
		$this->interval   = $interval;
	}

	/**
	 * Increment the progress bar ticks.
	 */
	public function tick() {
		if ( null === $this->progress_bar ) {
			$this->setup_progress_bar();
		}

		$this->progress_bar->tick();
		$this->total_ticks++;

		if ( $this->free_ticks && 0 === $this->total_ticks % $this->free_ticks ) {
			$this->free_memory();
		}
	}

	/**
	 * Get the progress bar tick count.
	 */
	public function current() {
		return $this->progress_bar ? $this->progress_bar->current() : 0;
	}

	/**
	 * Finish the current progress bar.
	 */
	public function finish() {
		if ( null !== $this->progress_bar ) {
			$this->progress_bar->finish();
		}

		$this->progress_bar = null;
	}

	/**
	 * Set the message used when creating the progress bar.
	 *
	 * @param string $message The message to be used when the next progress bar is created.
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Set the count for a new progress bar.
	 *
	 * @param integer $count The total number of ticks expected to complete.
	 */
	public function set_count( $count ) {
		$this->count = $count;
		$this->finish();
	}

	/**
	 * Set up the progress bar.
	 */
	protected function setup_progress_bar() {
		$this->progress_bar = \WP_CLI\Utils\make_progress_bar(
			$this->message,
			$this->count,
			$this->interval
		);
	}

	/**
	 * Reduce memory footprint by clearing the database query and object caches.
	 *
	 * @param integer $sleep_time The number of seconds to pause before resuming operation. Optional. Default 0.
	 */
	protected function free_memory( $sleep_time = 0 ) {
		if ( 0 < $sleep_time ) {
			WP_CLI::warning( sprintf( _n( 'Stopped the insanity for %d second', 'Stopped the insanity for %d seconds', $sleep_time, 'action-scheduler' ), $sleep_time ) );
			sleep( $sleep_time );
		}

		WP_CLI::warning( __( 'Attempting to reduce used memory...', 'action-scheduler' ) );

		/**
		 * @var $wpdb            \wpdb
		 * @var $wp_object_cache \WP_Object_Cache
		 */
		global $wpdb, $wp_object_cache;

		$wpdb->queries = array();

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops      = array();
		$wp_object_cache->stats          = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache          = array();

		if ( is_callable( array( $wp_object_cache, '__remoteset' ) ) ) {
			call_user_func( array( $wp_object_cache, '__remoteset' ) ); // important
		}
	}
}
