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
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::maybe_switch_on
 *
 * @group MaxCache
 */
class TestMaybeSwitchOn extends TestCase {
	/**
	 * Cleans up after each test.
	 *
	 * Here rather than at the end of the test body: an assertion that fails throws, and a form
	 * marker left behind would have every later test in the process read as a settings save.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		unset( $_POST['option_page'] );

		parent::tearDown();
	}

	/**
	 * Decides once, on the first server that can take the add-on.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   State for the case.
	 * @param array $expected What must happen.
	 *
	 * @return void
	 */
	public function testShouldDecideOnce( $config, $expected ) {
		$config = array_merge(
			[
				'stored'        => [],
				'may_manage'    => true,
				'held'          => false,
				'available'     => true,
				'can_switch_on' => true,
				'nginx'         => false,
				'form'          => false,
			],
			$config
		);

		/** @var array<string, mixed> $transients Transients written while the case runs. */
		$transients = [];

		Functions\when( 'wp_doing_ajax' )->justReturn( false );
		Functions\when( 'current_user_can' )->justReturn( $config['may_manage'] );
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		if ( $config['form'] ) {
			$_POST['option_page'] = 'wprocket';
		}

		// The settings page offers the switch unless the case says a host hides it: is_offered() reads
		// the same two filters the page reads, and a hidden switch is one nobody could turn back off.
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what this stub replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				switch ( $tag ) {
					case 'rocket_maxcache_available':
						// The host's own answer, which is not the same question as whether the module
						// is on disk: a host can keep the add-on off a platform that has it.
						return $config['available_filter'] ?? $config['available'];
					case 'rocket_display_input_maxcache':
						return $config['offered'] ?? true;
					case 'rocket_disable_htaccess':
						// A site that takes no rewrite of the file: the directives can never get there.
						return $config['writing_refused'] ?? false;
					default:
						return $value;
				}
			}
		);
		// Whether the plugin's own sanitize callback is registered: it is what drops the "ignore" key
		// again, so the key is only worth writing where it will be dropped.
		Functions\when( 'has_filter' )->justReturn( $config['sanitiser'] ?? true );
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_unslash' )->returnArg();
		Functions\when( 'rocket_get_constant' )->justReturn( 'wprocket' );
		Functions\when( 'get_transient' )->alias(
			function ( $key ) use ( &$transients, $config ) {
				if ( 'rocket_maxcache_switch_on_check' === $key ) {
					return $config['held'] ? 1 : ( $transients[ $key ] ?? false );
				}

				return $transients[ $key ] ?? false;
			}
		);
		Functions\when( 'set_transient' )->alias(
			function ( $key, $value, $expiration = 0 ) use ( &$transients ) {
				$transients[ $key ] = $expiration;

				return true;
			}
		);

		$maxcache = Mockery::mock( MaxCache::class );
		$maxcache->shouldReceive( 'is_available' )->andReturn( $config['available'] );
		$maxcache->shouldReceive( 'can_switch_on' )->andReturn( $config['can_switch_on'] );
		$maxcache->shouldReceive( 'is_nginx' )->andReturn( $config['nginx'] );

		// The row as it is read back after the write, which is what the decision is judged by: a write
		// the plugin refused leaves the row as it was.
		$row = $config['stored'];

		$reads = 0;

		$options_api = Mockery::mock( Options::class );
		// The row can change under this method: everything it asks between the first read and the write
		// runs third-party code, and this case has that code write a key of its own.
		$options_api->shouldReceive( 'get' )->with( 'settings', [] )->andReturnUsing(
			function () use ( &$row, &$reads, $config ) {
				++$reads;

				if ( $reads > 1 && ! empty( $config['row_changes'] ) ) {
					$row = array_merge( (array) $row, $config['row_changes'] );
				}

				return $row;
			}
		);

		if ( $expected['writes'] ) {
			$options_api->shouldReceive( 'set' )
				->once()
				->andReturnUsing(
					function ( $name, $value ) use ( $config, &$row ) {
						if ( ! ( $config['write_lands'] ?? true ) ) {
							return;
						}

						// What the plugin's own sanitize callback does with the escape-hatch key
						// wherever it is registered: takes it back out.
						if ( $config['sanitiser'] ?? true ) {
							unset( $value['ignore'] );
						}

						$row = $value;
					}
				)
				->with(
					'settings',
					// Everything already stored has to survive: this writes the whole array back. The
					// "ignore" key rides along where the plugin's sanitize callback is registered, so
					// that it does not treat this as a form submission; it is dropped there and never
					// stored, which is why it is not written where that callback is absent.
					Mockery::on(
						function ( $value ) use ( $config ) {
							$written = [ 'maxcache' => 1 ];

							if ( $config['sanitiser'] ?? true ) {
								$written['ignore'] = 1;
							}

							return array_merge( $config['stored'], $config['row_changes'] ?? [], $written ) === $value;
						}
					)
				);
		} else {
			$options_api->shouldNotReceive( 'set' );
		}

		$subscriber = new Subscriber( $maxcache, $options_api );

		$subscriber->maybe_switch_on();

		/*
		 * The settings page reads the option through this callback, and without it a page rendered in
		 * this same request would show the switch off while the row says otherwise — and a save from
		 * that page would then make the off permanent.
		 */
		// What the rest of the request is told about the switch: only a write the row took counts.
		$announced = $expected['told'] ?? $expected['writes'];

		$this->assertSame( $announced ? 1 : null, $subscriber->return_switched_on( null ) );


		$this->assertSame(
			$expected['hold'],
			$transients['rocket_maxcache_switch_on_check'] ?? null,
			'The hold on re-checking is what decides whether a fresh install ever switches on.'
		);

		$this->assertSame(
			$announced,
			isset( $transients['rocket_maxcache_notice'] ),
			'The notice is only queued when the add-on actually took over.'
		);
	}
}
