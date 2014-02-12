<?php

/**
 * General API functions for scheduling actions
 */

/**
 * Schedule an action to run one time
 *
 * @param int $timestamp When the job will run
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function wc_schedule_single_action( $timestamp, $hook, $args = array(), $group = '' ) {
	return ActionScheduler::factory()->single( $hook, $args, $timestamp, $group );
}

/**
 * Schedule a recurring action
 *
 * @param int $timestamp When the first instance of the job will run
 * @param int $interval_in_seconds How long to wait between runs
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function wc_schedule_recurring_action( $timestamp, $interval_in_seconds, $hook, $args = array(), $group = '' ) {
	return ActionScheduler::factory()->recurring( $hook, $args, $timestamp, $interval_in_seconds, $group );
}

/**
 * Schedule an action that recurs on a cron-like schedule.
 *
 * @param int $timestamp The schedule will start on or after this time
 * @param string $schedule A cron-link schedule string
 * @see http://en.wikipedia.org/wiki/Cron
 *   *    *    *    *    *    *
 *   ┬    ┬    ┬    ┬    ┬    ┬
 *   |    |    |    |    |    |
 *   |    |    |    |    |    + year [optional]
 *   |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
 *   |    |    |    +---------- month (1 - 12)
 *   |    |    +--------------- day of month (1 - 31)
 *   |    +-------------------- hour (0 - 23)
 *   +------------------------- min (0 - 59)
 * @param string $hook The hook to trigger
 * @param array $args Arguments to pass when the hook triggers
 * @param string $group The group to assign this job to
 *
 * @return string The job ID
 */
function wc_schedule_cron_action( $timestamp, $schedule, $hook, $args = array(), $group = '' ) {
	return ActionScheduler::factory()->cron( $hook, $args, $timestamp, $schedule, $group );
}

/**
 * Cancel the next occurrence of a job.
 *
 * @param string $hook The hook that the job will trigger
 * @param array $args Args that would have been passed to the job
 * @param string $group
 *
 * @return void
 */
function wc_unschedule_action( $hook, $args = array(), $group = '' ) {
	$params = array();
	if ( is_array($args) ) {
		$params['args'] = $args;
	}
	if ( !empty($group) ) {
		$params['group'] = $group;
	}
	$job_id = ActionScheduler::store()->find_action( $hook, $params );
	if ( empty($job_id) ) {
		return;
	}

	ActionScheduler::store()->cancel_action( $job_id );
}

/**
 * @param string $hook
 * @param array $args
 * @param string $group
 *
 * @return int|bool The timestamp for the next occurrence, or false if nothing was found
 */
function wc_next_scheduled_action( $hook, $args = NULL, $group = '' ) {
	$params = array();
	if ( is_array($args) ) {
		$params['args'] = $args;
	}
	if ( !empty($group) ) {
		$params['group'] = $group;
	}
	$job_id = ActionScheduler::store()->find_action( $hook, $params );
	if ( empty($job_id) ) {
		return false;
	}
	$job = ActionScheduler::store()->fetch_action( $job_id );
	$next = $job->get_schedule()->next();
	if ( $next ) {
		return $next->getTimestamp();
	}
	return false;
}