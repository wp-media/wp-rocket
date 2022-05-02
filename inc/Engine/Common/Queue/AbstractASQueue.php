<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\Queue;

use ActionScheduler_Store;

abstract class AbstractASQueue implements QueueInterface {

	/**
	 * Queue shared Group.
	 *
	 * @var string
	 */
	protected $group = 'rocket';

	/**
	 * Enqueue an action to run one time, as soon as possible
	 *
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @return string The action ID.
	 */
	public function add_async( $hook, $args = [] ) {
		return as_enqueue_async_action( $hook, $args, $this->group );
	}

	/**
	 * Schedule an action to run once at some time in the future
	 *
	 * @param int    $timestamp When the job will run.
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @return string The action ID.
	 */
	public function schedule_single( $timestamp, $hook, $args = [] ) {
		return as_schedule_single_action( $timestamp, $hook, $args, $this->group );
	}

	/**
	 * Schedule a recurring action
	 *
	 * @param int    $timestamp When the first instance of the job will run.
	 * @param int    $interval_in_seconds How long to wait between runs.
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @return string The action ID.
	 */
	public function schedule_recurring( $timestamp, $interval_in_seconds, $hook, $args = [] ) {
		if ( $this->is_scheduled( $hook, $args ) ) {
			// TODO: When v3.3.0 from Action Scheduler is commonly used use the array notation for status to reduce search queries to one.
			$pending_actions = $this->search(
				[
					'hook'   => $hook,
					'status' => ActionScheduler_Store::STATUS_PENDING,
				],
				'ids'
			);

			if ( 1 < count( $pending_actions ) ) {
				$this->cancel_all( $hook, $args );
				return '';
			}

			$running_actions = $this->search(
				[
					'hook'   => $hook,
					'status' => ActionScheduler_Store::STATUS_RUNNING,
				],
				'ids'
			);

			if ( 1 === count( $pending_actions ) + count( $running_actions ) ) {
				return '';
			}

			$this->cancel_all( $hook, $args );
		}

		return as_schedule_recurring_action( $timestamp, $interval_in_seconds, $hook, $args, $this->group );
	}

	/**
	 * Checks if the hook is scheduled.
	 *
	 * @param string $hook The hook to check.
	 * @param array  $args Passed arguments.
	 *
	 * @return bool
	 */
	public function is_scheduled( $hook, $args = [] ) {
		if ( ! function_exists( 'as_has_scheduled_action' ) ) {
			return ! is_null( $this->get_next( $hook, $args ) );
		}

		return as_has_scheduled_action( $hook, $args, $this->group );
	}

	/**
	 * Schedule an action that recurs on a cron-like schedule.
	 *
	 * @param int    $timestamp The schedule will start on or after this time.
	 * @param string $cron_schedule A cron-link schedule string.
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
	 * @param string $hook The hook to trigger.
	 * @param array  $args Arguments to pass when the hook triggers.
	 * @return string The action ID
	 */
	public function schedule_cron( $timestamp, $cron_schedule, $hook, $args = [] ) {
		if ( $this->is_scheduled( $hook, $args ) ) {
			return '';
		}

		return as_schedule_cron_action( $timestamp, $cron_schedule, $hook, $args, $this->group );
	}

	/**
	 * Dequeue the next scheduled instance of an action with a matching hook (and optionally matching args and group).
	 *
	 * Any recurring actions with a matching hook should also be cancelled, not just the next scheduled action.
	 *
	 * While technically only the next instance of a recurring or cron action is unscheduled by this method, that will also
	 * prevent all future instances of that recurring or cron action from being run. Recurring and cron actions are scheduled
	 * in a sequence instead of all being scheduled at once. Each successive occurrence of a recurring action is scheduled
	 * only after the former action is run. As the next instance is never run, because it's unscheduled by this function,
	 * then the following instance will never be scheduled (or exist), which is effectively the same as being unscheduled
	 * by this method also.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $args Args that would have been passed to the job.
	 */
	public function cancel( $hook, $args = [] ) {
		as_unschedule_action( $hook, $args, $this->group );
	}

	/**
	 * Dequeue all actions with a matching hook (and optionally matching args and group) so no matching actions are ever run.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $args Args that would have been passed to the job.
	 */
	public function cancel_all( $hook, $args = [] ) {
		as_unschedule_all_actions( $hook, $args, $this->group );
	}

	/**
	 * Get the date and time for the next scheduled occurence of an action with a given hook
	 * (an optionally that matches certain args and group), if any.
	 *
	 * @param string $hook The hook that the job will trigger.
	 * @param array  $args Filter to a hook with matching args that will be passed to the job when it runs.
	 * @return int|null The date and time for the next occurrence, or null if there is no pending, scheduled action for the given hook.
	 */
	public function get_next( $hook, $args = null ) {

		$next_timestamp = as_next_scheduled_action( $hook, $args, $this->group );

		if ( is_numeric( $next_timestamp ) ) {
			return $next_timestamp;
		}

		return null;
	}

	/**
	 * Find scheduled actions
	 *
	 * @param array  $args Possible arguments, with their default values:
	 *        'hook' => '' - the name of the action that will be triggered
	 *        'args' => null - the args array that will be passed with the action
	 *        'date' => null - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'date_compare' => '<=' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='
	 *        'modified' => null - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime(). Used in UTC timezone.
	 *        'modified_compare' => '<=' - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='
	 *        'group' => '' - the group the action belongs to
	 *        'status' => '' - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
	 *        'claimed' => null - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID
	 *        'per_page' => 5 - Number of results to return
	 *        'offset' => 0
	 *        'orderby' => 'date' - accepted values are 'hook', 'group', 'modified', or 'date'
	 *        'order' => 'ASC'.
	 *
	 * @param string $return_format OBJECT, ARRAY_A, or ids.
	 * @return array
	 */
	public function search( $args = [], $return_format = OBJECT ) {
		return as_get_scheduled_actions( $args, $return_format );
	}

}
