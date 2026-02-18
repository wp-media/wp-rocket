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
	public function tear_down() {
		// Clear the cache between tests
		$reflection = new \ReflectionClass( Logger::class );

		if ( $reflection->hasProperty( 'debug_enabled_cache' ) ) {
			$cache_property = $reflection->getProperty( 'debug_enabled_cache' );
			$cache_property->setAccessible( true );
			$cache_property->setValue( null, null );
		}

		unset( $_SERVER['REQUEST_URI'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnCorrectDebugStatus( $config, $expected ) {
		$this->wp_rocket_debug = $config['WP_ROCKET_DEBUG'] ?? null;

		$_SERVER['REQUEST_URI'] = $config['REQUEST_URI'] ?? '/';

		$result = Logger::debug_enabled();

		$this->assertSame( $expected, $result );
	}
}
