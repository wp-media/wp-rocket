<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Logger\Logger;

use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration test class covering \WP_Rocket\Logger\Logger::debug_enabled
 *
 * @group Logger
 */
class DebugEnabledTest extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->reset_logger_cache();
	}

	public function tear_down() {
		$this->reset_logger_cache();

		unset( $_SERVER['REQUEST_URI'] );

		parent::tear_down();
	}

	/**
	 * Reset Logger static cache properties using reflection
	 */
	private function reset_logger_cache() {
		$reflection = new \ReflectionClass( Logger::class );

		if ( $reflection->hasProperty( 'debug_enabled_cache' ) ) {
			$cache_property = $reflection->getProperty( 'debug_enabled_cache' );
			$cache_property->setAccessible( true );
			$cache_property->setValue( null, null );
		}
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->wp_rocket_debug = $config['WP_ROCKET_DEBUG'] ?? null;

		$_SERVER['REQUEST_URI'] = $config['REQUEST_URI'] ?? '/';

		$result = Logger::debug_enabled();

		$this->assertSame( $expected, $result );
	}
}
