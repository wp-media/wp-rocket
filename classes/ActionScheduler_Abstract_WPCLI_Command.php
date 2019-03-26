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
	 * @var bool|string Enable timestamp, or timestamp format.
	 */
	protected $timestamp_string = false;

	/**
	 * Construct.
	 */
	function __construct( $args, $assoc_args ) {
		$this->args = $args;
		$this->assoc_args = $assoc_args;
		$this->timestamp_string = \WP_CLI\Utils\get_flag_value( $assoc_args, 'time', false );

		$this->execute();
	}

	/**
	 * Execute command.
	 */
	abstract public function execute();

	/**
	 * Wrapper for WP_CLI::log()
	 */
	function log( $message ) {
		WP_CLI::log( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Wrapper for WP_CLI::error()
	 */
	function error( $message ) {
		WP_CLI::error( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Wrapper for WP_CLI::success()
	 */
	function success( $message ) {
		WP_CLI::success( sprintf( '%s%s', $this->output_timestamp(), $message ) );
	}

	/**
	 * Print timestamp to CLI, if enabled.
	 *
	 * @return null|string
	 */
	protected function output_timestamp() {
		if ( empty( $this->timestamp_string ) )
			return null;

		if ( true === $this->timestamp_string ) {
			$this->timestamp_string = 'Y-m-d H:i:s T';
		}

		return '[' . as_get_datetime_object()->format( $this->timestamp_string ) . '] ';
	}

}
