<?php

/**
 * General API functions for scheduling tasks
 */

function schedule_single_task( $timestamp, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->single( $hook, $args, $timestamp, $group );
}

function schedule_recurring_task( $timestamp, $interval_in_seconds, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->recurring( $hook, $args, $timestamp, $interval_in_seconds, $group );
}

function schedule_cron_task( $timestamp, $schedule, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->cron( $hook, $args, $timestamp, $schedule, $group );
}