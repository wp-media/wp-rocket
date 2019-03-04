<?php


namespace Action_Scheduler\Migration;

use ActionScheduler_Logger as Logger;
use ActionScheduler_Store as Store;

/**
 * Class ActionScheduler_MigrationConfig
 *
 * A config builder for the ActionScheduler_MigrationRunner class
 */
class ActionScheduler_MigrationConfig {
	/** @var Store */
	private $source_store;

	/** @var Logger */
	private $source_logger;

	/** @var Store */
	private $destination_store;

	/** @var Logger */
	private $destination_logger;

	/** @var Progress bar */
	private $progress_bar;

	/** @var bool */
	private $dry_run = false;

	public function __construct() {

	}

	public function get_source_store() {
		if ( empty( $this->source_store ) ) {
			throw new \RuntimeException( __( 'Source store must be configured before running a migration', 'action-scheduler' ) );
		}

		return $this->source_store;
	}

	/**
	 * @param Store $store
	 */
	public function set_source_store( Store $store ) {
		$this->source_store = $store;
	}

	/**
	 * @return Logger
	 */
	public function get_source_logger() {
		if ( empty( $this->source_logger ) ) {
			throw new \RuntimeException( __( 'Source logger must be configured before running a migration', 'action-scheduler' ) );
		}

		return $this->source_logger;
	}

	/**
	 * @param Logger $logger
	 */
	public function set_source_logger( Logger $logger ) {
		$this->source_logger = $logger;
	}

	/**
	 * @return Store
	 */
	public function get_destination_store() {
		if ( empty( $this->destination_store ) ) {
			throw new \RuntimeException( __( 'Destination store must be configured before running a migration', 'action-scheduler' ) );
		}

		return $this->destination_store;
	}

	/**
	 * @param Store $store
	 */
	public function set_destination_store( Store $store ) {
		$this->destination_store = $store;
	}

	/**
	 * @return Logger
	 */
	public function get_destination_logger() {
		if ( empty( $this->destination_logger ) ) {
			throw new \RuntimeException( __( 'Destination logger must be configured before running a migration', 'action-scheduler' ) );
		}

		return $this->destination_logger;
	}

	/**
	 * @param Logger $logger
	 */
	public function set_destination_logger( Logger $logger ) {
		$this->destination_logger = $logger;
	}

	/**
	 * @return bool
	 */
	public function get_dry_run() {
		return $this->dry_run;
	}

	/**
	 * @param bool $dry_run
	 */
	public function set_dry_run( $dry_run ) {
		$this->dry_run = (bool) $dry_run;
	}

	/**
	 * @return ActionScheduler_WPCLI_ProgressBar
	 */
	public function get_progress_bar() {
		return $this->progress_bar;
	}

	/**
	 * @param ActionScheduler_WPCLI_ProgressBar $progress_bar
	 */
	public function set_progress_bar( ActionScheduler_WPCLI_ProgressBar $progress_bar ) {
		$this->progress_bar = $progress_bar;
	}

}