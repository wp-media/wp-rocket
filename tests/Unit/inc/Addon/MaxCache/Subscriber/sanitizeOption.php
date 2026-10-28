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
 * Test class covering \WP_Rocket\Addon\MaxCache\Subscriber::sanitize_option
 *
 * @group MaxCache
 */
class TestSanitizeOption extends TestCase {
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
	 * Normalises the submitted value the way the plugin does for every add-on.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Submitted input and server state.
	 * @param array $expected Expected result.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedInput( $config, $expected ) {
		$maxcache = Mockery::mock( MaxCache::class );
		// Not here: what was read is dropped once the row has been written, on the action for it.
		$maxcache->shouldReceive( 'forget' )->never();

		// Read from the row, not from the container's snapshot of it.
		$options_api = Mockery::mock( Options::class );
		$options_api->shouldReceive( 'get' )
			->with( 'settings', [] )
			->andReturn( null === $config['stored'] ? [] : [ 'maxcache' => $config['stored'] ] );

		$subscriber = new Subscriber( $maxcache, $options_api );

		if ( $config['form'] ) {
			$_POST['option_page'] = 'wprocket';
		}

		// The add-ons section is skipped without one, so a page that shows the switch has one.
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		// The marker the form carries where the switch was drawn: what the save is judged by, rather
		// than what the site looks like now.
		if ( ! empty( $config['marker'] ) ) {
			$config['input']['maxcache_offered'] = '';
		}
		// wpm_apply_filters_typed() is left alone: it is a real function that calls apply_filters(),
		// which is what this stub replaces.
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) use ( $config ) {
				return 'rocket_maxcache_available' === $tag ? ! empty( $config['offered'] ) : $value;
			}
		);
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'rocket_get_constant' )->justReturn( 'wprocket' );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\when( 'wp_unslash' )->returnArg();

		$result = $subscriber->sanitize_option( $config['input'] );


		$this->assertSame( $expected, $result['maxcache'] ?? null );
	}
}
