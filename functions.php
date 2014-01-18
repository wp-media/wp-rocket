<?php

/**
 * General API functions for scheduling tasks
 */

/**
 * @param int $timestamp When the job will run
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function schedule_single_task( $timestamp, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->single( $hook, $args, $timestamp, $group );
}

/**
 * @param int $timestamp When the first instance of the job will run
 * @param int $interval_in_seconds How long to wait between runs
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function schedule_recurring_task( $timestamp, $interval_in_seconds, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->recurring( $hook, $args, $timestamp, $interval_in_seconds, $group );
}

/**
 * @param int $timestamp The schedule will start on or after this time
 * @param string $schedule A cron-link schedule string
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function schedule_cron_task( $timestamp, $schedule, $hook, $args = array(), $group = '' ) {
	return TaskScheduler::factory()->cron( $hook, $args, $timestamp, $schedule, $group );
}