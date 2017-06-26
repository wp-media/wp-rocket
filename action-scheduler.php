<?php
/*
Plugin Name: Action Scheduler
Plugin URI: https://github.com/prospress/action-scheduler
Description: A robust action scheduler for WordPress
Author: Prospress
Author URI: http://prospress.com/
Version: 1.6.0
*/

if ( ! function_exists( 'action_scheduler_register_1_dot_6_dot_0' ) ) {

	if ( ! class_exists( 'ActionScheduler_Versions' ) ) {
		require_once( 'classes/ActionScheduler_Versions.php' );
		add_action( 'plugins_loaded', array( 'ActionScheduler_Versions', 'initialize_latest_version' ), 1, 0 );
	}

	add_action( 'plugins_loaded', 'action_scheduler_register_1_dot_6_dot_0', 0, 0 );

	function action_scheduler_register_1_dot_6_dot_0() {
		$versions = ActionScheduler_Versions::instance();
		$versions->register( '1.6.0', 'action_scheduler_initialize_1_dot_6_dot_0' );
	}

	function action_scheduler_initialize_1_dot_6_dot_0() {
		require_once( __DIR__ . '/vendor/autoload.php' );
		require_once( 'classes/ActionScheduler.php' );
		ActionScheduler::init( __FILE__ );
	}

}