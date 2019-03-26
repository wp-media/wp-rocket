<?php

/**
 * Action Scheduler abstract class for WP CLI commands.
 */
abstract class ActionScheduler_Abstract_WPCLI_Command {

	/**
	 * @var array
	 */
	protected $args;
	protected $assoc_args;

	/**
	 * @var bool Enable printing of timestamp.
	 */
	protected $timestamp = false;

	/**
	 * @var string Format of timestamp.
	 */
	protected $timestamp_format = 'Y-m-d H:i:s T';

	/**
	 * Construct.
	 */
	public function __construct( $args, $assoc_args ) {
		$this->args = $args;
		$this->assoc_args = $assoc_args;
		$this->timestamp = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'time', false );
		$this->timestamp_format = \WP_CLI\Utils\get_flag_value( $assoc_args, 'time-format', $this->timestamp_format );
	}

	/**
	 * Execute command.
	 */
	abstract public function execute();

	/**
	 * Wrapper for WP_CLI::log()
	 */
	protected function log( $message ) {
		WP_CLI::log( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Wrapper for WP_CLI::error()
	 */
	protected function error( $message ) {
		WP_CLI::error( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Wrapper for WP_CLI::success()
	 */
	protected function success( $message ) {
		WP_CLI::success( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Print timestamp to CLI, if enabled.
	 *
	 * @return string
	 */
	protected function output_timestamp() {
		if ( empty( $this->timestamp ) ) {
			return '';
		}

		return '[' . as_get_datetime_object()->format( $this->timestamp_format ) . '] ';
	}

}
