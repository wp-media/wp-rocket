<?php
/*
Plugin Name: Task Scheduler
Plugin URI: https://github.com/flightless/delayed-job
Description: A robust task scheduler for WordPress
Author: Flightless
Author URI: http://flightless.us/
Version: 0.1
*/

function task_scheduler_initialize() {
	spl_autoload_register('task_scheduler_autoload');
	TaskScheduler::init( __FILE__ );
}

function task_scheduler_autoload( $class ) {

	if ( strpos( $class, 'TaskScheduler' ) !== 0 ) {
		return;
	}
	$dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR;

	if ( file_exists( $dir.$class.'.php' ) ) {
		include( $dir.$class.'.php' );
		return;
	}
}
add_action( 'plugins_loaded', 'task_scheduler_initialize', 10, 0 );