<?php

namespace WP_Rocket\Engine\Debug\RUCSS;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

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
			'rocket_last_rucss_job_added_time' => [ 'log_last_added_job_time', 10, 2 ],
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
		if ( ! $is_success ) {
			return;
		}

		$this->options->set( 'last_rucss_job_added', $timestamp );
		$this->options_api->set( 'debug', $this->options->get_options() );
	}
}
