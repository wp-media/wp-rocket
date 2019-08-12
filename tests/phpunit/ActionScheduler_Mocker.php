<?php
/**
 * ActionScheduler_Mocker
 */

defined( 'ABSPATH' ) || exit;

/**
 * ActionScheduler_Mocker class.
 */
class ActionScheduler_Mocker {

	/**
	 * Do not run queues via async requests.
	 *
	 * @param ActionScheduler_Store $store
	 */
	public static function get_queue_runner( ActionScheduler_Store $store = null ) {

		if ( ! $store ) {
			$store = ActionScheduler_Store::instance();
		}

		return new ActionScheduler_QueueRunner( $store, null, null, self::get_async_request_queue_runner( $store ) );
	}

	/**
	 * Get an instance of the mock queue runner
	 *
	 * @param ActionScheduler_Store $store
	 */
	protected static function get_async_request_queue_runner( ActionScheduler_Store $store ) {
		return new ActionScheduler_Mock_AsyncRequest_QueueRunner( $store );
	}
}
