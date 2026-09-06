<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\MaxCache\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\MaxCache\MaxCache;
use WP_Rocket\Addon\MaxCache\Subscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::maybe_restore_rules
 *
 * @group MaxCache
 */
class TestMaybeRestoreRules extends TestCase {
	/**
	 * Checks when the file is written again, and when trying is given up on.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Capability, switch, hold, add-on state and what the write is allowed to do.
	 * @param array $expected Whether the plugin writes the file, how, and whether it stops trying.
	 *
	 * @return void
	 */
	public function testShouldFlushWhenExpected( $config, $expected ) {
		$dropped  = [];
		$told     = 0;
		$flushed  = false;
		$removed  = false;
		$stripped = false;
		$held     = false;

		$was_apache           = $GLOBALS['is_apache'] ?? null;
		$GLOBALS['is_apache'] = $config['apache'];

		Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Functions\when( 'current_user_can' )->justReturn( $config['allowed'] );

		Functions\when( 'get_transient' )->justReturn( $config['held'] );
		Functions\when( 'set_transient' )->alias(
			function () use ( &$held ) {
				$held = true;
			}
		);
		Functions\when( 'get_home_path' )->justReturn( '/var/www/html/' );
		/**
		 * Assigned below, and read by the stub that stands in for the plugin's writer.
		 *
		 * @var \WP_Rocket\Addon\MaxCache\Subscriber|null $subscriber
		 */
		$subscriber = null;

		Functions\when( 'flush_rocket_htaccess' )->alias(
			// Announces the write the way the real function does, so that what the daemon is told
			// counts every path that reaches it, not only the ones this class calls directly.
			function ( $remove_rules = false ) use ( $config, &$flushed, &$removed, &$subscriber ) {
				$flushed = true;
				$removed = (bool) $remove_rules;

				if ( $config['writable'] && null !== $subscriber ) {
					$subscriber->after_flush( '/var/www/html/.htaccess', true );
				}

				return $config['writable'];
			}
		);
		// The announcement goes with the state: dropped as soon as the add-on is no longer delivering.
		Functions\when( 'delete_transient' )->alias(
			function ( $key ) use ( &$dropped ) {
				$dropped[] = $key;

				return true;
			}
		);
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what the stub below replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				return 'rocket_disable_htaccess' === $tag ? $config['writing_refused'] : $value;
			}
		);

		$maxcache = Mockery::mock( MaxCache::class );
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$maxcache->shouldReceive( 'decision_recorded' )->andReturn( $config['decided'] );
		$maxcache->shouldReceive( 'is_available' )->andReturn( $config['available'] );
		$maxcache->shouldReceive( 'is_nginx' )->andReturn( $config['nginx'] );
		// Called by after_flush(), which the flush stub above fires as the real function does.
		$maxcache->shouldReceive( 'forget' )->andReturn( null );
		$maxcache->shouldReceive( 'decision_recorded' )->andReturn( $config['decided'] );
		$maxcache->shouldReceive( 'notify_configd' )->andReturnUsing(
			function () use ( &$told ) {
				++$told;

				return true;
			}
		);
		$maxcache->shouldReceive( 'htaccess_path' )->andReturn( '/var/www/html/.htaccess' );
		// Writes the file itself where the plugin refuses to, and only its own section goes.
		$maxcache->shouldReceive( 'remove_directives' )->andReturnUsing(
			function () use ( $config, &$stripped ) {
				$stripped = true;

				return $config['writable'];
			}
		);
		$maxcache->shouldReceive( 'is_enabled' )->andReturn( $config['enabled'] );
		// Read from the file, so the answer changes with what the write left there.
		$maxcache->shouldReceive( 'has_directives' )->andReturnUsing(
			function () use ( $config, &$flushed, &$stripped ) {
				if ( $stripped ) {
					return ! $config['writable'];
				}

				return $flushed ? ! ( $config['writable'] && $config['flush_clears'] ) : $config['directives'];
			}
		);

		$options_api = Mockery::mock( Options::class );
		// The switch as this case has it: the subscriber reads it through the add-on's own accessor.
		$maxcache->shouldReceive( 'options' )->andReturn( new Options_Data( [ 'maxcache' => $config['option'] ] ) );

		$subscriber = new Subscriber( $maxcache, $options_api );

		$subscriber->maybe_restore_rules();

		if ( null === $was_apache ) {
			unset( $GLOBALS['is_apache'] );
		} else {
			$GLOBALS['is_apache'] = $was_apache;
		}

		$actual = [
			'flushed'  => $flushed,
			'removed'  => $removed,
			'stripped' => $stripped,
			'held'     => $held,
		];

		// How many times the daemon was woken, where the case says: one change of the file is one.
		if ( array_key_exists( 'told', $expected ) ) {
			$actual['told'] = $told;
		}

		$this->assertSame( $expected, $actual );

		// The announcement outlives a refusal: display_notice() asks is_enabled() when it renders, so a
		// stale notice cannot be shown — while dropping it here would be for good.
		$this->assertNotContains( 'rocket_maxcache_notice', $dropped );
	}
}
