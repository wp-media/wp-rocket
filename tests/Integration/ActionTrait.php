<?php

namespace WP_Rocket\Tests\Integration;

use ReflectionObject;
use WP_Rocket\Tests\SettingsTrait;
use WP_Rocket\Tests\StubTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

Trait ActionTrait {

	protected $original_wp_action;

	protected function unregisterAllCallbacksActionsExcept( $event_name, $method_name, $priority = 10 ) {
		global $wp_filter;

		if(! key_exists($event_name, $wp_filter)) {
			return;
		}

		$original_wp_action = $wp_filter[ $event_name ]->callbacks;

		if(! $original_wp_action) {
			return;
		}

		$this->original_wp_action = $original_wp_action;

		foreach ( $this->original_wp_action[ $priority ] as $key => $config ) {

			// Skip if not this tests callback.
			if ( substr( $key, - strlen( $method_name ) ) !== $method_name ) {
				continue;
			}

			$wp_filter[ $event_name ]->callbacks = [
				$priority => [ $key => $config ],
			];
		}
	}

	protected function restoreWpAction( $event_name ) {
		global $wp_filter;
		$wp_filter[ $event_name ]->callbacks = $this->original_wp_action;

	}
}
