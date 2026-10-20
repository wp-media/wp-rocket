<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\MaxCache;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering what happens to a real .htaccess when the module or the plugin goes away.
 *
 * Both paths are only themselves against a real file: one hands delivery back to the plugin by
 * rewriting the whole block, the other takes this add-on's section out by its own markers.
 *
 * @group MaxCache
 * @group Addon
 */
class Test_PutsTheRulesBack extends TestCase {
	use AddOnStandTrait;

	/**
	 * Transients this class writes, cleared between tests.
	 *
	 * @var array
	 */
	protected static $transients = [
		'rocket_maxcache_restore_check' => null,
		'rocket_maxcache_switch_on_check' => null,
		'rocket_maxcache_notice' => null,
	];

	/**
	 * Sets the stage for each test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->given_the_add_on_stand();

		// Deterministic rather than left to the order the tests run in: a hold left behind makes the
		// corrective check exit before it does anything.
		delete_transient( 'rocket_maxcache_restore_check' );
	}

	/**
	 * Puts the file, the server type and the container's own callbacks back.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->then_the_stand_comes_down();

		delete_transient( 'rocket_maxcache_restore_check' );

		parent::tear_down();
	}

	/**
	 * The module leaving the host hands delivery back to the plugin without anyone acting.
	 *
	 * @return void
	 */
	public function testShouldGiveDeliveryBackWhenTheModuleIsGone() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );
		flush_rocket_htaccess();

		$this->assertStringContainsString( '# BEGIN MAx Cache', $this->file_contents() );

		// The package is removed from the host between two admin requests.
		$this->maxcache->answer_mode( 'none' );

		// Preconditions of the corrective check, asserted rather than assumed: without either of them
		// it returns having done nothing, and every assertion below would pass on that.
		$this->assertTrue( rocket_valid_key(), 'the licence the add-on gate asks for' );
		$this->assertTrue( current_user_can( 'rocket_manage_options' ), 'the capability that gate asks for' );

		$this->stand_in->maybe_restore_rules();

		$contents = $this->file_contents();

		$this->assertStringNotContainsString( 'MAx Cache', $contents );
		$this->assertStringContainsString( 'RewriteRule', $contents );
		$this->assertStringContainsString( '# Someone else', $contents );
		// Held afterwards, or every admin request would pay the probes and the read for this answer.
		$this->assertNotFalse( get_transient( 'rocket_maxcache_restore_check' ) );
	}

	/**
	 * A site that takes no rewrite of the file has only its own section taken out.
	 *
	 * @return void
	 */
	public function testShouldTakeOutOnlyItsSectionWhereTheFileIsNotRewritten() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );
		flush_rocket_htaccess();

		$this->maxcache->answer_mode( 'none' );

		add_filter( 'rocket_disable_htaccess', '__return_true' );

		// Preconditions of the corrective check, asserted rather than assumed: without either of them
		// it returns having done nothing, and every assertion below would pass on that.
		$this->assertTrue( rocket_valid_key(), 'the licence the add-on gate asks for' );
		$this->assertTrue( current_user_can( 'rocket_manage_options' ), 'the capability that gate asks for' );

		$this->stand_in->maybe_restore_rules();

		remove_filter( 'rocket_disable_htaccess', '__return_true' );

		$contents = $this->file_contents();

		$this->assertStringNotContainsString( 'MAx Cache', $contents );
		// The block itself stays: taking it away would take browser caching and compression with it.
		$this->assertStringContainsString( '# BEGIN WP Rocket', $contents );
		$this->assertStringContainsString( 'ExpiresByType', $contents );
	}

	/**
	 * Deactivating the plugin leaves nothing of this add-on's in the file.
	 *
	 * @return void
	 */
	public function testShouldLeaveNothingBehindOnDeactivation() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );
		flush_rocket_htaccess();

		$this->assertStringContainsString( '# BEGIN MAx Cache', $this->file_contents() );

		// What the plugin's own writer leaves alone: a block whose closing marker is gone.
		$mangled = str_replace( '# END WP Rocket', '', $this->file_contents() );
		$this->given_a_file( $mangled );

		$this->stand_in->remove_on_deactivation();

		$contents = $this->file_contents();

		$this->assertStringNotContainsString( 'MAx Cache', $contents );
		$this->assertStringContainsString( '# Someone else', $contents );
	}

}

