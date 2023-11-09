<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

Trait FilterTrait {

	protected $original_wp_filter;

	protected $original_wp_priorities;

	protected function unregisterAllCallbacksExcept( $event_name, $method_name, $priority = 10 ) {
		global $wp_filter;
		$this->original_wp_filter = $wp_filter[ $event_name ]->callbacks;
		$wp_hooks = $wp_filter[ $event_name ];
		$reflection = new ReflectionClass($wp_hooks);
		try {

		} catch (ReflectionException $e) {
			$property = $reflection->getProperty('priorities');
			$property->setAccessible(true);
			$this->original_wp_priorities = $property->getValue($wp_hooks);
			$priorities = $property->getValue($wp_hooks);
		}


		foreach ( $this->original_wp_filter[ $priority ] as $key => $config ) {

			// Skip if not this tests callback.
			if ( substr( $key, - strlen( $method_name ) ) !== $method_name ) {
				continue;
			}

			$wp_filter[ $event_name ]->callbacks = [
				$priority => [ $key => $config ],
			];
		}

		if (! $this->original_wp_priorities) {
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

		$property->setValue($wp_hooks, $priorities);
	}

	protected function restoreWpFilter( $event_name ) {
		global $wp_filter;
		$wp_filter[ $event_name ]->callbacks = $this->original_wp_filter;
		if (! $this->original_wp_priorities) {
			return;
		}
		$wp_hooks = $wp_filter[ $event_name ];
		$reflection = new ReflectionClass($wp_hooks);
		$property = $reflection->getProperty('priorities');
		$property->setAccessible(true);
		$property->setValue($wp_hooks, $this->original_wp_priorities);
	}
}
