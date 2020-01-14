---
title: Action Scheduler - Background Processing Job Queue for WordPress
---
## WordPress Job Queue with Background Processing

Action Scheduler is a library for triggering a WordPress hook to run at some time in the future (or as soon as possible, in the case of an async action). Each hook can be scheduled with unique data, to allow callbacks to perform operations on that data. The hook can also be scheduled to run on one or more occassions.

Think of it like an extension to `do_action()` which adds the ability to delay and repeat a hook.

It just so happens, this functionality also creates a robust job queue for background processing large queues of tasks in WordPress. With the additional of logging and an [administration interface](/admin/), that also provide tracability on your tasks processed in the background.

### Battle-Tested Background Processing

Every month, Action Scheduler processes millions of payments for [Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/), webhooks for [WooCommerce](https://wordpress.org/plugins/woocommerce/), as well as emails and other events for a range of other plugins.

It's been seen on live sites processing queues in excess of 50,000 jobs and doing resource intensive operations, like processing payments and creating orders, in 10 concurrent queues at a rate of over 10,000 actions / hour without negatively impacting normal site operations.

This is all possible on infrastructure and WordPress sites outside the control of the plugin author.

Action Scheduler is specifically designed for distribution in WordPress plugins (and themes) - no server access required. If your plugin needs background processing, especially of large sets of tasks, Action Scheduler can help.

### How it Works

Action Scheduler stores the hook name, arguments and scheduled date for an action that should be triggered at some time in the future.

The scheduler will attempt to run every minute by attaching itself as a callback to the `'action_scheduler_run_schedule'` hook, which is scheduled using WordPress's built-in [WP-Cron](http://codex.wordpress.org/Function_Reference/wp_cron) system. Once per minute on, it will also check on the `'shutdown'` hook of WP Admin requests whether there are pending actions, and if there are, it will initiate a queue via an async loopback request.

When triggered, Action Scheduler will check for scheduled actions that have a due date at or before this point in time i.e. actions scheduled to run now or at sometime in the past. Action scheduled to run asynchronously, i.e. not scheduled, have a zero date, meaning they will always be due no matter when the check occurs.

### Batch Processing Background Jobs

If there are actions to be processed, Action Scheduler will stake a unique claim for a batch of 25 actions and begin processing that batch. The PHP process spawned to run the batch will then continue processing batches of 25 actions until it uses 90% of available memory or has been processing for 30 seconds.

At that point, if there are additional actions to process, an asynchronous loopback request will be made to the site to continue processing actions in a new request.

This process and the loopback requests will continue until all actions are processed.

### Housekeeping

Before processing a batch, the scheduler will remove any existing claims on actions which have been sitting in a queue for more than five minutes (or more specifically, 10 times the allowed time limit, which defaults to 30 seconds).

Action Scheduler will also trash any actions which were completed or canceled more than a month ago.

If an action runs for more than 5 minutes, Action Scheduler will assume the action has timed out and will mark it as failed. However, if all callbacks attached to the action were to successfully complete sometime after that 5 minute timeout, its status would later be updated to completed.

### Traceable Background Processing

Did your background job run?

Never be left wondering with Action Scheduler's built-in record keeping.

All events for each action are logged in the `actionscheduler_logs` table and displayed in the [administration interface](/admin/).

The events logged by default include when an action:
 * is created
 * starts (including details of how it was run, e.g. via [WP CLI](/wp-cli/) or WP Cron)
 * completes
 * fails

If it fails with an error that can be recorded, that error will be recorded in the log and visible in administration interface, making it possible to trace what went wrong at some point in the past on a site you didn't have access to in the past.

Actions can also be grouped together using a custom taxonomy named `action-group`.

## Credits

Action Scheduler is developed and maintained by folks at [Automattic](http://automattic.com/) with significant early development completed by [Flightless](https://flightless.us/).

Collaboration is cool. We'd love to work with you to improve Action Scheduler. [Pull Requests](https://github.com/woocommerce/action-scheduler/pulls) welcome.
