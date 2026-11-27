<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\MaxCache;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\MaxCache::get_mode
 *
 * @group MaxCache
 */
class TestGetMode extends TestCase {
	/**
	 * Checks which build is found, for a given set of probe answers.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $errno    Error number the probe answers, per path.
	 * @param string $expected Expected mode.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedMode( $errno, $expected ) {
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->returnArg( 2 );

		$maxcache = new class( Mockery::mock( Options::class ), $errno ) extends MaxCache {
			/**
			 * Error number to answer, per path.
			 *
			 * @var array
			 */
			private $errno;

			/**
			 * Constructor.
			 *
			 * @param Options $options_api Options instance.
			 * @param array   $errno       Error number to answer, per path.
			 */
			public function __construct( Options $options_api, array $errno ) {
				parent::__construct( $options_api );

				$this->errno = $errno;
			}

			/**
			 * {@inheritdoc}
			 */
			/**
			 * Answers whichever errno the case asked for.
			 *
			 * @param string   $path    Absolute path to test.
			 * @param float    $timeout Connection timeout in seconds.
			 * @param int|null $errno   Set to the connection error code.
			 *
			 * @param-out int $errno
			 *
			 * @return resource|false
			 */
			protected function open_socket( string $path, float $timeout, &$errno = null ) {
				// ENOENT is what a path that is not there answers.
				$errno = isset( $this->errno[ $path ] ) ? (int) $this->errno[ $path ] : 2;

				return false;
			}
		};

		$this->assertSame( $expected, $maxcache->get_mode() );
	}

	/**
	 * Checks that a callback on the filter can ask this class what it found.
	 *
	 * @return void
	 */
	public function testShouldAnswerACallbackThatAsksThisClassWhatItFound() {
		$calls    = 0;
		$answered = null;
		$maxcache = $this->maxcache_finding_apache();

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( &$calls, &$answered, $maxcache ) {
				if ( 'rocket_maxcache_mode' !== $tag ) {
					return $value;
				}

				++$calls;

				// Self-limited: a class that re-enters here would otherwise run until the stack gives out,
				// and a hung process says less than a count does.
				if ( $calls < 3 ) {
					$answered = $maxcache->get_mode();
				}

				return $value;
			}
		);

		$mode = $maxcache->get_mode();

		$this->assertSame( 1, $calls, 'The filter must be applied once per request.' );
		$this->assertSame( 'apache', $mode );
	}

	/**
	 * Checks the same for the filter applied while the probes are still running.
	 *
	 * @return void
	 */
	public function testShouldAnswerACallbackOnTheSocketPathFilter() {
		$calls    = 0;
		$maxcache = $this->maxcache_finding_apache();

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( &$calls, $maxcache ) {
				if ( 'rocket_maxcache_configd_socket_path' !== $tag ) {
					return $value;
				}

				++$calls;

				// Self-limited, as above: without an answer here this would run until the stack gives out.
				if ( $calls < 3 ) {
					$maxcache->is_nginx();
				}

				return $value;
			}
		);

		$mode = $maxcache->get_mode();

		$this->assertSame( 1, $calls, 'The filter must be applied once per request.' );
		$this->assertSame( 'apache', $mode );
	}

	/**
	 * Returns an instance whose probes answer the way a host with the Apache build does.
	 *
	 * @return MaxCache
	 */
	private function maxcache_finding_apache(): MaxCache {
		return new class( Mockery::mock( Options::class ) ) extends MaxCache {
			/**
			 * Answers the way a root-owned file inside a searchable directory does.
			 *
			 * @param string   $path    Absolute path to test.
			 * @param float    $timeout Connection timeout in seconds.
			 * @param int|null $errno   Set to the connection error code.
			 *
			 * @param-out int $errno
			 *
			 * @return resource|false
			 */
			protected function open_socket( string $path, float $timeout, &$errno = null ) {
				$errno = MaxCache::VERSION_FILE_APACHE === $path ? 13 : 2;

				return false;
			}
		};
	}
}
