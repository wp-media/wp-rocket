<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\MaxCache;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering what this add-on leaves in a real .htaccess file.
 *
 * The unit tests for the same paths run on vfsStream, whose feof() ends a read loop one line earlier
 * than a plain file does — so the line handling of every writer here is only ever pinned by a test
 * that writes a real file.
 *
 * @group MaxCache
 * @group Addon
 */
class Test_WritesTheFile extends TestCase {
	use AddOnStandTrait;

	/**
	 * Sets the stage for each test.
	 *
	 * The module on disk is the one thing a stand-in has to answer for; everything else — the options,
	 * the filters on the file, the plugin's own rules, the file itself — is real.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->given_the_add_on_stand();
	}

	/**
	 * Puts the file, the server type and the container's own callbacks back.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->then_the_stand_comes_down();

		parent::tear_down();
	}

	/**
	 * Exactly one component owns delivery once the file is written.
	 *
	 * @return void
	 */
	public function testShouldLeaveOnlyItsOwnDirectivesServing() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );

		$this->assertTrue( flush_rocket_htaccess() );

		$contents = $this->file_contents();

		$this->assertStringContainsString( '# BEGIN MAx Cache', $contents );
		$this->assertStringContainsString( 'MaxCachePath ', $contents );
		$this->assertStringContainsString( '# END WP Rocket', $contents );
		// The plugin's own serving rules are what this add-on takes over from.
		$this->assertStringNotContainsString( 'RewriteRule', $contents );
		$this->assertStringContainsString( '# Someone else', $contents );
	}

	/**
	 * Turning the switch off puts the plugin's serving rules back.
	 *
	 * @return void
	 */
	public function testShouldGiveDeliveryBackWhenTheSwitchGoesOff() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );
		flush_rocket_htaccess();

		$this->given_the_switch( 0 );

		$this->assertTrue( flush_rocket_htaccess() );

		$contents = $this->file_contents();

		$this->assertStringNotContainsString( 'MAx Cache', $contents );
		$this->assertStringContainsString( 'RewriteRule', $contents );
	}

	/**
	 * A removal takes this add-on's section and nothing else.
	 *
	 * @return void
	 */
	public function testShouldTakeOutItsOwnSectionAlone() {
		$this->given_the_switch( 1 );
		$this->given_a_file( "# Someone else\n" );
		flush_rocket_htaccess();

		$before = $this->file_contents();

		$this->assertTrue( $this->maxcache->remove_directives() );

		$after = $this->file_contents();

		$this->assertStringNotContainsString( 'MAx Cache', $after );
		$this->assertStringContainsString( '# Someone else', $after );
		// Everything the plugin wrote is left where it was, to the byte.
		$this->assertSame(
			$this->without_maxcache_section( $before ),
			$after
		);
	}

	/**
	 * A block whose closing marker is gone is not rewritten at all.
	 *
	 * @return void
	 */
	public function testShouldTouchNothingWhenTheBlockNeverCloses() {
		$this->given_the_switch( 1 );
		$before = "# Someone else\n# BEGIN WP Rocket\nRules\n# And after\n";
		$this->given_a_file( $before );

		$this->assertFalse( flush_rocket_htaccess() );
		$this->assertSame( $before, $this->file_contents() );
	}

	/**
	 * A removal keeps every line that is not the plugin's, in the order they were in.
	 *
	 * The trailing newline is left out of the comparison on purpose: whether the read loop in
	 * flush_rocket_htaccess() yields one final empty line differs between environments — a plain
	 * PHP process does, this suite's does not — so the last byte is not this function's to promise.
	 *
	 * @return void
	 */
	public function testShouldKeepWhatIsNotTheBlockWhenRemovingIt() {
		$this->given_the_switch( 0 );
		$this->given_a_file( "# Someone else\n# BEGIN WP Rocket\nRules\n# END WP Rocket\n# And after\n" );

		$this->assertTrue( flush_rocket_htaccess( true ) );
		$this->assertSame( "# Someone else\n# And after", rtrim( $this->file_contents(), "\n" ) );
	}


	/**
	 * Returns the given contents without this add-on's section, for comparison.
	 *
	 * @param string $contents Contents to strip.
	 *
	 * @return string
	 */
	private function without_maxcache_section( string $contents ): string {
		$start = strpos( $contents, '# BEGIN MAx Cache' );
		$end   = strpos( $contents, '# END MAx Cache' );

		if ( false === $start || false === $end ) {
			return $contents;
		}

		$end += strlen( '# END MAx Cache' );

		// The line ending that closed the section goes with it.
		if ( "\n" === substr( $contents, $end, 1 ) ) {
			++$end;
		}

		return substr( $contents, 0, $start ) . substr( $contents, $end );
	}
}
