<?php
/**
 * ActionScheduler_Mock_AsyncRequest_QueueRunner
 */

defined( 'ABSPATH' ) || exit;

/**
 * ActionScheduler_Mock_AsyncRequest_QueueRunner class.
 */
class ActionScheduler_Mock_AsyncRequest_QueueRunner extends ActionScheduler_AsyncRequest_QueueRunner {

	/**
	 * Do not run queues via async requests.
	 */
	protected function allow() {
		return false;
	}
}
