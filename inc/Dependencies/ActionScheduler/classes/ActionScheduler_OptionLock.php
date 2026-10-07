<?php

/**
 * Provide a way to set simple transient locks to block behaviour
 * for up-to a given duration.
 *
 * Class ActionScheduler_OptionLock
 *
 * @since 3.0.0
 */
class ActionScheduler_OptionLock extends ActionScheduler_Lock {

	/**
	 * Set a lock using options for a given amount of time (60 seconds by default).
	 *
	 * Using an autoloaded option avoids running database queries or other resource intensive tasks
	 * on frequently triggered hooks, like 'init' or 'shutdown'.
	 *
	 * For example, ActionScheduler_QueueRunner->maybe_dispatch_async_request() uses a lock to avoid
	 * calling ActionScheduler_QueueRunner->has_maximum_concurrent_batches() every time the 'shutdown',
	 * hook is triggered, because that method calls ActionScheduler_QueueRunner->store->get_claim_count()
	 * to find the current number of claims in the database.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @bool True if lock value has changed, false if not or if set failed.
	 */
	public function set( $lock_type ) {
		global $wpdb;

		$now                 = time();
		$lock_key            = $this->get_key( $lock_type );
		$existing_lock_value = $this->get_existing_lock( $lock_type, $now );
		$new_lock_value      = $this->new_lock_value( $lock_type, $now );

		// The lock may not exist yet, or may have been deleted.
		if ( null === $existing_lock_value ) {
			$inserted = (bool) $wpdb->insert(
				$wpdb->options,
				array(
					'option_name'  => $lock_key,
					'option_value' => $new_lock_value,
					'autoload'     => 'no',
				)
			);

			// Sync cache as necessary.
			if ( $inserted ) {
				$ttl = $this->get_expiration_from( $new_lock_value ) - $now;
				if ( $ttl > 0 ) {
					wp_cache_set( $lock_key, $new_lock_value, 'action_scheduler_locks', $ttl );
				}
			}

			return $inserted;
		}

		if ( $this->get_expiration_from( $existing_lock_value ) >= $now ) {
			return false;
		}

		// Otherwise, try to obtain the lock.
		$updated = (bool) $wpdb->update(
			$wpdb->options,
			array( 'option_value' => $new_lock_value ),
			array(
				'option_name'  => $lock_key,
				'option_value' => $existing_lock_value,
			)
		);

		// Sync cache as necessary.
		if ( $updated ) {
			$ttl = $this->get_expiration_from( $new_lock_value ) - $now;
			if ( $ttl > 0 ) {
				wp_cache_set( $lock_key, $new_lock_value, 'action_scheduler_locks', $ttl );
			}
		} else {
			// Compare-and-swap failed — another process acquired the lock between our read and write.
			// Invalidate cache: the expired value may still be live in WP object cache (TTL=1 edge case) before the winner's wp_cache_set executes.
			wp_cache_delete( $lock_key, 'action_scheduler_locks' );
		}

		return $updated;
	}

	/**
	 * If a lock is set, return the timestamp it was set to expiry.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @return bool|int False if no lock is set, otherwise the timestamp for when the lock is set to expire.
	 */
	public function get_expiration( $lock_type ) {
		return $this->get_expiration_from( (string) $this->get_existing_lock( $lock_type, time() ) );
	}

	/**
	 * Given the lock string, derives the lock expiration timestamp (or false if it cannot be determined).
	 *
	 * @param string $lock_value String containing a timestamp, or pipe-separated combination of unique value and timestamp.
	 *
	 * @return false|int
	 */
	private function get_expiration_from( $lock_value ) {
		$lock_string = explode( '|', $lock_value );
		$count       = count( $lock_string );

		// Old style lock?
		if ( 1 === $count && is_numeric( $lock_string[0] ) ) {
			return (int) $lock_string[0];
		}

		// New style lock?
		if ( 2 === $count && is_numeric( $lock_string[1] ) ) {
			return (int) $lock_string[1];
		}

		return false;
	}

	/**
	 * Get the key to use for storing the lock in the transient
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @return string
	 */
	protected function get_key( $lock_type ) {
		return sprintf( 'action_scheduler_lock_%s', $lock_type );
	}

	/**
	 * Supplies the existing lock value, or null if not set.
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @param int    $now       The timestamp to use.
	 *
	 * @return string|null
	 */
	private function get_existing_lock( $lock_type, int $now ) {
		global $wpdb;

		$lock_key = $this->get_key( $lock_type );
		$cached   = wp_cache_get( $lock_key, 'action_scheduler_locks' );
		if ( false !== $cached ) {
			return (string) $cached;
		}

		$value = null;
		// Now grab the existing lock value, if there is one.
		// get_var() returns null for the empty string ('') so we must use get_row().
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT option_value FROM $wpdb->options WHERE option_name = %s",
				$lock_key
			)
		);

		if ( $row ) {
			$value = $row->option_value;
			// Sync cache as necessary.
			$ttl = $this->get_expiration_from( $value ) - $now;
			if ( $ttl > 0 ) {
				wp_cache_set( $lock_key, $value, 'action_scheduler_locks', $ttl );
			}
		}

		return $value;
	}

	/**
	 * Supplies a lock value consisting of a unique value and the current timestamp, which are separated by a pipe
	 * character.
	 *
	 * Example: (string) "649de012e6b262.09774912|1688068114"
	 *
	 * @param string $lock_type A string to identify different lock types.
	 * @param int    $now       The timestamp to use.
	 *
	 * @return string
	 */
	private function new_lock_value( $lock_type, int $now ): string {
		return uniqid( '', true ) . '|' . ( $now + $this->get_duration( $lock_type ) );
	}
}
