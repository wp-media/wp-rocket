<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionClass;
use ReflectionException;

Trait IsolateHookTrait {

	protected $original_wp_filter;

	protected $original_wp_priorities;

	protected function unregisterAllCallbacksExceptMulti( $event_name, $methods = [] ) {
		global $wp_filter;
		$this->original_wp_filter = $wp_filter[ $event_name ]->callbacks;
		$wp_filter[ $event_name ]->callbacks = [];

		foreach ( $methods as $priority => $method_name ) {
			foreach ( $this->original_wp_filter[ $priority ] as $key => $config ) {

				// Skip if not this tests callback.
				if ( substr( $key, - strlen( $method_name ) ) !== $method_name ) {
					continue;
				}

				// Accumulate every matching callback at this priority. Several objects can hook
				// a method of the same name at the same priority, so replacing instead of
				// accumulating would drop the callback under test.
				$wp_filter[ $event_name ]->callbacks[ $priority ][ $key ] = $config;
			}
		}

		try {
			$wp_hooks = $wp_filter[ $event_name ];
			$reflection = new ReflectionClass($wp_hooks);
			$priorities_property = $reflection->getProperty('priorities');
			$priorities_property->setAccessible(true);
			$this->original_wp_priorities = $priorities_property->getValue($wp_hooks);
			$priorities = $priorities_property->getValue($wp_hooks);
		} catch (ReflectionException $e) {
			return;
		}

		foreach ($this->original_wp_priorities as $priority) {
			if ( key_exists($priority, $wp_filter[ $event_name ]->callbacks)) {
				continue;
			}

			$priorities = array_values(array_filter($priorities, function ($current) use ($priority) {
				return $current !== $priority;
			}));

		}

		$priorities_property->setValue($wp_hooks, $priorities);
	}

	/**
	 * Strips every callback registered on the given hook, saving the originals so
	 * {@see self::restoreWpHook()} can put them back on tear down.
	 *
	 * Use this to isolate a method under test from ALL incidental subscribers on a shared
	 * hook (e.g. `update_option_wp_rocket_settings`), rather than removing them one by one —
	 * so a subscriber added by a future feature can't silently reintroduce a side effect
	 * (a DB query against an uninstalled table, a cache purge, etc.).
	 *
	 * @param string $event_name Hook name.
	 *
	 * @return void
	 */
	protected function unregisterAllCallbacks( $event_name ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $event_name ] ) ) {
			$this->original_wp_filter     = null;
			$this->original_wp_priorities = null;

			return;
		}

		$wp_hooks                 = $wp_filter[ $event_name ];
		$this->original_wp_filter = $wp_hooks->callbacks;
		$wp_hooks->callbacks      = [];

		try {
			$reflection          = new ReflectionClass( $wp_hooks );
			$priorities_property = $reflection->getProperty( 'priorities' );

			// ✅ PHP 8.1+: setAccessible() is not needed and deprecated
			// ✅ PHP 7.4: Still works (setAccessible does nothing but no warning)
			if ( PHP_VERSION_ID < 80100 ) {
				$priorities_property->setAccessible( true );
			}

			$this->original_wp_priorities = $priorities_property->getValue( $wp_hooks );
			$priorities_property->setValue( $wp_hooks, [] );
		} catch ( ReflectionException $e ) {
			return;
		}
	}

	protected function unregisterAllCallbacksExcept( $event_name, $method_name, $priority = 10 ) {
		global $wp_filter;
		$this->original_wp_filter = $wp_filter[ $event_name ]->callbacks;

		$kept = [];
		foreach ( $this->original_wp_filter[ $priority ] as $key => $config ) {

			// Skip if not this tests callback.
			if ( substr( $key, - strlen( $method_name ) ) !== $method_name ) {
				continue;
			}

			// Accumulate every matching callback. Several objects can hook a method of the
			// same name at the same priority (e.g. two `on_update` on `wp_rocket_upgrade`),
			// so replacing instead of accumulating would drop the callback under test.
			$kept[ $key ] = $config;
		}

		if ( ! empty( $kept ) ) {
			$wp_filter[ $event_name ]->callbacks = [ $priority => $kept ];
		}

		try {
			$wp_hooks = $wp_filter[ $event_name ];
			$reflection = new ReflectionClass($wp_hooks);
			$priorities_property = $reflection->getProperty('priorities');

			// ✅ PHP 8.1+: setAccessible() is not needed and deprecated
			// ✅ PHP 7.4: Still works (setAccessible does nothing but no warning)
			if ( PHP_VERSION_ID < 80100 ) {
				$priorities_property->setAccessible( true );
			}

			$this->original_wp_priorities = $priorities_property->getValue($wp_hooks);
			$priorities = $priorities_property->getValue($wp_hooks);
		} catch (ReflectionException $e) {
			return;
		}

		foreach ($this->original_wp_priorities as $priority) {
			if ( key_exists($priority, $wp_filter[ $event_name ]->callbacks)) {
				continue;
			}

			$priorities = array_values(array_filter($priorities, function ($current) use ($priority) {
				return $current !== $priority;
			}));

		}

		$priorities_property->setValue($wp_hooks, $priorities);
	}

	protected function restoreWpHook($event_name ) {
		global $wp_filter;

		// Nothing was captured (hook was not registered when isolated): nothing to restore.
		if ( null === $this->original_wp_filter || ! isset( $wp_filter[ $event_name ] ) ) {
			return;
		}

		$wp_filter[ $event_name ]->callbacks = $this->original_wp_filter;
		if (! $this->original_wp_priorities) {
			return;
		}
		$wp_hooks = $wp_filter[ $event_name ];
		$reflection = new ReflectionClass($wp_hooks);
		$priorities_property = $reflection->getProperty('priorities');

		// ✅ PHP 8.1+: setAccessible() is not needed and deprecated
		// ✅ PHP 7.4: Still works (setAccessible does nothing but no warning)
		if ( PHP_VERSION_ID < 80100 ) {
			$priorities_property->setAccessible( true );
		}

		$priorities_property->setValue($wp_hooks, $this->original_wp_priorities);
	}
}
