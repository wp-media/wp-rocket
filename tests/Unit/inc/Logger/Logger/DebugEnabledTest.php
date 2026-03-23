<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Logger\Logger;

use Brain\Monkey\Functions;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Logger\Logger::debug_enabled
 *
 * @group Logger
 */
class DebugEnabledTest extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$_SERVER['HTTP_HOST'] = 'example.org';

		$this->reset_logger_cache();
	}

	protected function tearDown(): void {
		$this->reset_logger_cache();

		unset( $_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'] );

		parent::tearDown();
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
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( isset( $config['WP_ROCKET_DEBUG'] ) ) {
			define( 'WP_ROCKET_DEBUG', $config['WP_ROCKET_DEBUG'] );
		}

		$_SERVER['REQUEST_URI'] = $config['REQUEST_URI'] ?? '/';

		$result = Logger::debug_enabled();

		$this->assertSame( $expected, $result );

		// Test caching behavior if specified
		if ( isset( $config['test_cache'] ) && $config['test_cache'] ) {
			// Call again without re-mocking to verify cache is used
			$result2 = Logger::debug_enabled();
			$this->assertSame( $expected, $result2 );
		}
	}
}
