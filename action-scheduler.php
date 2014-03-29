<?php
/*
Plugin Name: Action Scheduler
Plugin URI: https://github.com/flightless/action-scheduler
Description: A robust action scheduler for WordPress
Author: Flightless
Author URI: http://flightless.us/
Version: 1.1
*/

if ( !function_exists('action_scheduler_register_1_dot_1') ) {

	if ( !class_exists( 'ActionScheduler_Versions' ) ) {
		require_once('classes/ActionScheduler_Versions.php');
		add_action( 'plugins_loaded', array( 'ActionScheduler_Versions', 'initialize_latest_version' ), 1, 0 );
	}

	add_action( 'plugins_loaded', 'action_scheduler_register_1_dot_1', 0, 0 );

	function action_scheduler_register_1_dot_1() {
		$versions = ActionScheduler_Versions::instance();
		$versions->register( '1.1', 'action_scheduler_initialize_1_dot_1' );
	}

	function action_scheduler_initialize_1_dot_1() {
		require_once('classes/ActionScheduler.php');
		ActionScheduler::init( __FILE__ );
	}

}