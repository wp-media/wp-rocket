## API Functions

### Action Scheduler API vs. WP-Cron API

The Action Scheduler API functions are designed to mirror the WordPress [WP-Cron API functions](http://codex.wordpress.org/Category:WP-Cron_Functions).

Functions return similar values and accept similar arguments to their WP-Cron counterparts. The notable differences are:

* `as_schedule_single_action()` & `as_schedule_recurring_action()` will return the post ID of the scheduled action rather than boolean indicating whether the event was scheduled
* `as_schedule_recurring_action()` takes an interval in seconds as the recurring interval rather than an arbitrary string
* `as_schedule_single_action()` & `as_schedule_recurring_action()` can accept a `$group` parameter to group different actions for the one plugin together.
* the `wp_` prefix is substituted with `as_` and the term `event` is replaced with `action`

### API Function Availability

As mentioned in the [Usage - Load Order](#load-order) section, Action Scheduler will initialize itself on the `'init'` hook with priority `1`. While API functions are loaded prior to this and call be called, they should not be called until after `'init'` with priority `1`, because each component, like the data store, has not yet been initialized.

Do not use Action Scheduler API functions prior to `'init'` hook with priority `1`. Doing so could lead to unexpected results, like data being stored in the incorrect location.

#### Function Reference / `as_schedule_single_action()`

##### Description

Schedule an action to run one time.

##### Usage

```php
<?php as_schedule_single_action( $timestamp, $hook, $args, $group ); ?>
````

##### Parameters

- **$timestamp** (integer)(required) The Unix timestamp representing the date you want the action to run. Default: _none_.
- **$hook** (string)(required) Name of the action hook. Default: _none_.
- **$args** (array) Arguments to pass to callbacks when the hook triggers. Default: _`array()`_.
- **$group** (array) The group to assign this job to. Default: _''_.

##### Return value

(integer) the action's ID in the [posts](http://codex.wordpress.org/Database_Description#Table_Overview) table.


#### Function Reference / `as_schedule_recurring_action()`

##### Description

Schedule an action to run repeatedly with a specified interval in seconds.

##### Usage

```php
<?php as_schedule_recurring_action( $timestamp, $interval_in_seconds, $hook, $args, $group ); ?>
````

##### Parameters

- **$timestamp** (integer)(required) The Unix timestamp representing the date you want the action to run. Default: _none_.
- **$interval_in_seconds** (integer)(required) How long to wait between runs. Default: _none_.
- **$hook** (string)(required) Name of the action hook. Default: _none_.
- **$args** (array) Arguments to pass to callbacks when the hook triggers. Default: _`array()`_.
- **$group** (array) The group to assign this job to. Default: _''_.

##### Return value

(integer) the action's ID in the [posts](http://codex.wordpress.org/Database_Description#Table_Overview) table.


#### Function Reference / `as_schedule_cron_action()`

##### Description

Schedule an action that recurs on a cron-like schedule.

##### Usage

```php
<?php as_schedule_cron_action( $timestamp, $schedule, $hook, $args, $group ); ?>
````

##### Parameters

- **$timestamp** (integer)(required) The Unix timestamp representing the date you want the action to run. Default: _none_.
- **$schedule** (string)(required) $schedule A cron-link schedule string, see http://en.wikipedia.org/wiki/Cron. Default: _none_.
- **$hook** (string)(required) Name of the action hook. Default: _none_.
- **$args** (array) Arguments to pass to callbacks when the hook triggers. Default: _`array()`_.
- **$group** (array) The group to assign this job to. Default: _''_.

##### Return value

(integer) the action's ID in the [posts](http://codex.wordpress.org/Database_Description#Table_Overview) table.


#### Function Reference / `as_unschedule_action()`

##### Description

Cancel the next occurrence of a job.

##### Usage

```php
<?php as_unschedule_action( $hook, $args, $group ); ?>
````

##### Parameters

- **$hook** (string)(required) Name of the action hook. Default: _none_.
- **$args** (array) Arguments to pass to callbacks when the hook triggers. Default: _`array()`_.
- **$group** (array) The group to assign this job to. Default: _''_.

##### Return value

(null)


#### Function Reference / `as_next_scheduled_action()`

##### Description

Returns the next timestamp for a scheduled action.

##### Usage

```php
<?php as_next_scheduled_action( $hook, $args, $group ); ?>
````

##### Parameters

- **$hook** (string)(required) Name of the action hook. Default: _none_.
- **$args** (array) Arguments to pass to callbacks when the hook triggers. Default: _`array()`_.
- **$group** (array) The group to assign this job to. Default: _''_.

##### Return value

(integer|boolean) The timestamp for the next occurrence, or false if nothing was found.


#### Function Reference / `as_get_scheduled_actions()`

##### Description

Find scheduled actions.

##### Usage

```php
<?php as_get_scheduled_actions( $args, $return_format ); ?>
````

##### Parameters

- **$args** (array) Arguments to search and filter results by. Possible arguments, with their default values:
    * `'hook' => ''` - the name of the action that will be triggered
    * `'args' => NULL` - the args array that will be passed with the action
    * `'date' => NULL` - the scheduled date of the action. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime().
    * `'date_compare' => '<=`' - operator for testing "date". accepted values are '!=', '>', '>=', '<', '<=', '='
    * `'modified' => NULL` - the date the action was last updated. Expects a DateTime object, a unix timestamp, or a string that can parsed with strtotime().
    * `'modified_compare' => '<='` - operator for testing "modified". accepted values are '!=', '>', '>=', '<', '<=', '='
    * `'group' => ''` - the group the action belongs to
    * `'status' => ''` - ActionScheduler_Store::STATUS_COMPLETE or ActionScheduler_Store::STATUS_PENDING
    * `'claimed' => NULL` - TRUE to find claimed actions, FALSE to find unclaimed actions, a string to find a specific claim ID
    * `'per_page' => 5` - Number of results to return
    * `'offset' => 0`
    * `'orderby' => 'date'` - accepted values are 'hook', 'group', 'modified', or 'date'
    * `'order' => 'ASC'`
- **$return_format** (string) The format in which to return the scheduled actions: 'OBJECT', 'ARRAY_A', or 'ids'. Default: _'OBJECT'_.

##### Return value

(array) Array of the actions matching the criteria specified with `$args`.


## Performance Tuning

By default, Action Scheduler will process a minimum of 1,200 actions per hour. On servers which allow long running PHP processes, this will be significantly higher as processes will be able loop over queues indefinitely.

The batch size and number of concurrent queues that may be processed simultaneously is low by default to ensure the scheduler runs on low powered servers; however, you can configure these settings to increase performance on your site.

#### Increasing Batch Size

By default, Action Scheduler will claim a batch of 20 actions. This small batch size is to minimise the risk of causing a fatal error due to memory exhaustion.

If you know the callbacks attached to your actions use very little memory, or you've tested the number of actions you can process before memory limits are exceeded, you can increase the batch size using the `'action_scheduler_queue_runner_batch_size'` filter.

For example, to increase the batch size to 100, we can use the following function:

```
<?php
function eg_increase_action_scheduler_batch_size( $batch_size ) {
	return 100;
}
add_filter( 'action_scheduler_queue_runner_batch_size', 'eg_increase_action_scheduler_batch_size' );
?>
```

### Increasing Concurrent Batches

By default, Action Scheduler will run up to 5 concurrent batches of actions. This is to prevent consuming all the available connections or processes on your webserver.

However, your server may allow a large number of connection, for example, because it has a high value for Apache's `MaxClients` setting or PHP-FPM's `pm.max_children` setting.

If this is the case, you can use the `'action_scheduler_queue_runner_concurrent_batches'` filter to increase the number of conncurrent batches allowed, and therefore speed up processing large numbers of actions scheduled to be processed simultaneously.

For example, to increase the allowed number of concurrent queues to 25, we can use the following code:

```
<?php
function eg_increase_action_scheduler_concurrent_batches( $concurrent_batches ) {
	return 25;
}
add_filter( 'action_scheduler_queue_runner_concurrent_batches', 'eg_increase_action_scheduler_concurrent_batches' );
?>
```