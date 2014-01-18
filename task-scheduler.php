<?php
/*
Plugin Name: Task Scheduler
Plugin URI: https://github.com/flightless/delayed-job
Description: A robust task scheduler for WordPress
Author: Flightless
Author URI: http://flightless.us/
Version: 1.0-dev
*/

if ( !function_exists('task_scheduler_register_1_dot_0_dev') ) {

	if ( !class_exists('TaskScheduler_Versions') ) {
		require_once('classes/TaskScheduler_Versions.php');
		add_action( 'plugins_loaded', array( 'TaskScheduler_Versions', 'initialize_latest_version' ), 1, 0 );
	}

	add_action( 'plugins_loaded', 'task_scheduler_register_1_dot_0_dev', 0, 0 );

	function task_scheduler_register_1_dot_0_dev() {
		$versions = TaskScheduler_Versions::instance();
		$versions->register( '1.0-dev', 'task_scheduler_initialize_1_dot_0_dev' );
	}

	function task_scheduler_initialize_1_dot_0_dev() {
		require_once('classes/TaskScheduler.php');
		TaskScheduler::init( __FILE__ );
	}

}