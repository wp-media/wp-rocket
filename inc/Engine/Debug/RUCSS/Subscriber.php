<?php

namespace WP_Rocket\Engine\Debug\RUCSS;

use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Logger\Logger;

class Subscriber implements Subscriber_Interface {

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Instantiate the class
	 *
	 * @param Options_Data $options Options instance.
	 * @param Options      $options_api Options instance.
	 */
	public function __construct( Options_Data $options, Options $options_api ) {
		$this->options     = $options;
		$this->options_api = $options_api;
	}

	/**
	 * Returns an array of events this listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_last_saas_job_added_time'          => [ 'log_last_added_job_time', 10, 2 ],
			'rocket_saas_process_pending_jobs_start'   => [ 'log_process_pending_job_start_time', 10, 1 ],
			'rocket_saas_process_pending_jobs_end'     => [ 'log_process_pending_job_end_time', 10, 1 ],
			'rocket_saas_check_job_status_end'         => [ 'log_check_job_status_end', 10, 1 ],
			'rocket_saas_process_on_submit_jobs_start' => [ 'log_process_on_submit_start', 10, 1 ],
			'rocket_saas_process_on_submit_jobs_end'   => [ 'log_process_on_submit_end', 10, 1 ],
		];
	}

	/**
	 * Saves the last time a new job was added to rucss table.
	 *
	 * @param mixed  $is_success New job status: ID of inserted row if successfully added; false otherwise.
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_last_added_job_time( $is_success, $timestamp ) {
		if ( Logger::debug_enabled() ) {
			if ( ! $is_success ) {
				return;
			}

			$this->options->set( 'last_rucss_job_added', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}

	/**
	 * Saves the time when the process pending jobs started.
	 *
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_process_pending_job_start_time( $timestamp ) {
		if ( Logger::debug_enabled() ) {
			$this->options->set( 'rucss_process_pending_jobs_start', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}

	/**
	 * Saves the time when the process pending jobs ended.
	 *
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_process_pending_job_end_time( $timestamp ) {
		if ( Logger::debug_enabled() ) {
			$this->options->set( 'rucss_process_pending_jobs_end', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}

	/**
	 * Saves the time when the check job status ended.
	 *
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_check_job_status_end( $timestamp ) {
		if ( Logger::debug_enabled() ) {
			$this->options->set( 'rucss_check_job_status_end', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}

	/**
	 * Saves the time when the process on submit jobs started.
	 *
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_process_on_submit_start( $timestamp ) {
		if ( Logger::debug_enabled() ) {
			$this->options->set( 'rucss_process_on_submit_jobs_start', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}

	/**
	 * Saves the time when the process on submit jobs ended.
	 *
	 * @param string $timestamp Current timestamp.
	 * @return void
	 */
	public function log_process_on_submit_end( $timestamp ) {
		if ( Logger::debug_enabled() ) {
			$this->options->set( 'rucss_process_on_submit_jobs_end', $timestamp );
			$this->options_api->set( 'debug', $this->options->get_options() );
		}
	}
}
