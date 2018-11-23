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