<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\MaxCache;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering the one decision this add-on takes without being asked.
 *
 * The row it writes is the plugin's own, sanitized by the plugin's own callback, so what is stored
 * afterwards is worth asking of a real row rather than of a mock. Which states are decided and which
 * are held is covered case by case in the unit tests for the same method.
 *
 * @group MaxCache
 * @group Addon
 */
class Test_SwitchesItselfOn extends TestCase {
	use AddOnStandTrait;

	/**
	 * Transients this class writes.
	 *
	 * @var array
	 */
	protected static $transients = [
		'rocket_maxcache_switch_on_check' => null,
		'rocket_maxcache_notice'          => null,
	];

	/**
	 * Sets the stage for each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->given_the_add_on_stand();

		delete_transient( 'rocket_maxcache_switch_on_check' );
		delete_transient( 'rocket_maxcache_notice' );

		// A row with something in it and no answer about this switch: an install that predates the
		// add-on. An empty row is the request that activated the plugin, which is a case of its own.
		$this->options_api->set( 'settings', [ 'cache_mobile' => 1, 'do_caching_mobile_files' => 0 ] );
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->then_the_stand_comes_down();

		delete_transient( 'rocket_maxcache_switch_on_check' );
		delete_transient( 'rocket_maxcache_notice' );

		parent::tear_down();
	}

	/**
	 * On a host that has the module the add-on switches itself on, and says so.
	 *
	 * @return void
	 */
	public function testShouldSwitchOnAndQueueTheNotice() {
		$this->assertTrue( rocket_valid_key(), 'the licence the add-on gate asks for' );

		$this->stand_in->maybe_switch_on();

		$stored = (array) $this->options_api->get( 'settings', [] );

		$this->assertSame( 1, (int) $stored['maxcache'] );
		// Everything already in the row survives the write, and the plugin's own sanitize callback
		// drops the "ignore" key the add-on rides along with.
		$this->assertSame( 1, (int) $stored['cache_mobile'] );
		$this->assertArrayNotHasKey( 'ignore', $stored );
		$this->assertNotFalse( get_transient( 'rocket_maxcache_notice' ) );
	}

	/**
	 * A site that takes no rewrite of .htaccess is not switched on behind the owner's back.
	 *
	 * There the directives can never reach the file, so the switch would sit on for a delivery that
	 * cannot happen — and the answer would count as this site's own from then on.
	 *
	 * @return void
	 */
	public function testShouldNotSwitchOnWhereTheFileIsNeverWritten() {
		add_filter( 'rocket_disable_htaccess', '__return_true' );

		$this->stand_in->maybe_switch_on();

		remove_filter( 'rocket_disable_htaccess', '__return_true' );

		$stored = (array) $this->options_api->get( 'settings', [] );

		$this->assertArrayNotHasKey( 'maxcache', $stored );
		$this->assertFalse( get_transient( 'rocket_maxcache_notice' ) );
		// Held, so the next admin request does not pay the probes again for the same answer.
		$this->assertNotFalse( get_transient( 'rocket_maxcache_switch_on_check' ) );
	}
}
