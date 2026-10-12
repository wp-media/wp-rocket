<?php

namespace WP_Rocket\Tests\Unit\inc\functions\htaccess;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use org\bovigo\vfs\vfsStream;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::flush_rocket_htaccess
 *
 * @uses ::rocket_htaccess_needed_without_apache
 *
 * @group Functions
 * @group htaccess
 * @group vfs
 */
class Test_FlushRocketHtaccess extends TestCase {
	/**
	 * Server type as the plugin reads it.
	 *
	 * @var bool|null
	 */
	private $was_apache;

	/**
	 * Whether a listener says the file is needed on a server that is not Apache.
	 *
	 * @var bool
	 */
	private $needed_without_apache = false;

	/**
	 * What that listener was told about the call.
	 *
	 * @var bool|null
	 */
	private $asked_about_removal;

	protected function setUp(): void {
		parent::setUp();

		vfsStream::setup( 'home', 0777 );

		$this->was_apache      = $GLOBALS['is_apache'] ?? null;
		$GLOBALS['is_apache']  = true;

		Functions\when( 'get_home_path' )->justReturn( 'vfs://home/' );
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value, $removing = null ) {
				if ( 'rocket_htaccess_needed_without_apache' === $tag ) {
					$this->asked_about_removal = $removing;

					return $this->needed_without_apache;
				}

				return $value;
			}
		);
	}

	protected function tearDown(): void {
		// Left set, it would follow this process into every later test that writes a file.
		vfsStream::setQuota( -1 );

		if ( null === $this->was_apache ) {
			unset( $GLOBALS['is_apache'] );
		} else {
			$GLOBALS['is_apache'] = $this->was_apache;
		}

		parent::tearDown();
	}

	/**
	 * Off Apache the file is left alone unless something reads it.
	 *
	 * @return void
	 */
	public function testShouldLeaveTheFileAloneOffApacheWhenNothingReadsIt() {
		$this->given_a_file( "# BEGIN WP Rocket\nRules\n# END WP Rocket\n" );
		$GLOBALS['is_apache'] = false;

		Actions\expectDone( 'rocket_after_flush_htaccess' )->never();

		$this->assertFalse( flush_rocket_htaccess() );
		$this->assertSame( "# BEGIN WP Rocket\nRules\n# END WP Rocket\n", $this->file_contents() );
	}

	/**
	 * A listener is told which kind of call is asking, so it can answer for a removal alone.
	 *
	 * @return void
	 */
	public function testShouldTellTheListenerWhetherTheCallIsARemoval() {
		$this->given_a_file( "# BEGIN WP Rocket\nRules\n# END WP Rocket\n" );
		$GLOBALS['is_apache']        = false;
		$this->needed_without_apache = true;

		flush_rocket_htaccess( true );

		$this->assertTrue( $this->asked_about_removal );
		$this->assertSame( '', $this->file_contents() );
	}

	/**
	 * A removal that empties the file writes no bytes, and reports the removal it carried out.
	 *
	 * @return void
	 */
	public function testShouldAnnounceTheWriteWhenTheRemovalEmptiesTheFile() {
		$this->given_a_file( "# BEGIN WP Rocket\nRules\n# END WP Rocket\n" );

		Actions\expectDone( 'rocket_after_flush_htaccess' )
			->once()
			->with( 'vfs://home/.htaccess', true );

		$this->assertTrue( flush_rocket_htaccess( true ) );
		$this->assertSame( '', $this->file_contents() );
	}

	/**
	 * A removal keeps everything that is not the plugin's.
	 *
	 * @return void
	 */
	public function testShouldKeepWhatIsNotTheBlockWhenRemovingIt() {
		$this->given_a_file( "# Someone else\n# BEGIN WP Rocket\nRules\n# END WP Rocket\n# And after\n" );

		$this->assertTrue( flush_rocket_htaccess( true ) );
		// Trailing newline left out of the comparison: vfsStream ends the read loop a line earlier than
		// a plain file does, so a real .htaccess keeps its final newline and this wrapper drops it.
		$this->assertSame( "# Someone else\n# And after", rtrim( $this->file_contents(), "\n" ) );
	}

	/**
	 * Nothing of the plugin's in the file and a removal asked for: no markers are written around it.
	 *
	 * @return void
	 */
	public function testShouldWriteNoMarkersWhenRemovingFromAFileThatHasNone() {
		$this->given_a_file( "# Someone else\n" );

		Actions\expectDone( 'rocket_after_flush_htaccess' )
			->once()
			->with( 'vfs://home/.htaccess', false );

		$this->assertTrue( flush_rocket_htaccess( true ) );
		$this->assertSame( "# Someone else\n", $this->file_contents() );
	}

	/**
	 * A removal is never "no change" while the markers are still there.
	 *
	 * @return void
	 */
	public function testShouldRemoveMarkersLeftAroundAnEmptyBlock() {
		$this->given_a_file( "# BEGIN WP Rocket\n\n# END WP Rocket\n" );

		Actions\expectDone( 'rocket_after_flush_htaccess' )
			->once()
			->with( 'vfs://home/.htaccess', true );

		flush_rocket_htaccess( true );

		$this->assertStringNotContainsString( 'WP Rocket', $this->file_contents() );
	}

	/**
	 * The plugin does not write a file it has been told not to touch, and takes its rules out of one.
	 *
	 * @return void
	 */
	public function testShouldStillRemoveTheRulesWhereWritingTheFileIsDisabled() {
		$this->given_a_file( "# BEGIN WP Rocket\nRules\n# END WP Rocket\n" );

		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) {
				return 'rocket_disable_htaccess' === $tag ? true : $value;
			}
		);

		flush_rocket_htaccess( true );

		$this->assertSame( '', $this->file_contents() );
	}

	/**
	 * A block that opens and never closes is not removed: where it ends cannot be told.
	 *
	 * @return void
	 */
	public function testShouldLeaveAnUnclosedBlockAloneWhenRemoving() {
		$this->given_a_file( "# Someone else\n# BEGIN WP Rocket\nRules\n# And after\n" );

		Actions\expectDone( 'rocket_after_flush_htaccess' )->never();

		$this->assertFalse( flush_rocket_htaccess( true ) );
		$this->assertSame( "# Someone else\n# BEGIN WP Rocket\nRules\n# And after\n", $this->file_contents() );
	}

	/**
	 * The same block, on a write: what follows the marker is nobody else's to lose either.
	 *
	 * @return void
	 */
	public function testShouldLeaveAnUnclosedBlockAloneWhenWriting() {
		$this->given_a_file( "# Someone else\n# BEGIN WP Rocket\nRules\n# And after\n" );

		// Enough of the plugin for the rules to be built: this is the one case here that writes them.
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			define( 'WP_ROCKET_VERSION', '3.24' );
		}

		Functions\when( 'get_bloginfo' )->justReturn( 'UTF-8' );
		Functions\when( 'get_rocket_option' )->justReturn( 0 );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		// Ends the serving rules where they begin: what goes between the markers does not matter here.
		Functions\when( 'is_multisite' )->justReturn( true );

		Actions\expectDone( 'rocket_after_flush_htaccess' )->never();

		$this->assertFalse( flush_rocket_htaccess() );
		$this->assertSame( "# Someone else\n# BEGIN WP Rocket\nRules\n# And after\n", $this->file_contents() );
	}

	/**
	 * A write cut short by a quota leaves a broken file, and says so.
	 *
	 * @return void
	 */
	public function testShouldReportAWriteThatWasCutShort() {
		$this->given_a_file( "# Someone else\n" );

		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			define( 'WP_ROCKET_VERSION', '3.24' );
		}

		Functions\when( 'get_bloginfo' )->justReturn( 'UTF-8' );
		Functions\when( 'get_rocket_option' )->justReturn( 0 );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		Functions\when( 'is_multisite' )->justReturn( true );

		// Room for a fraction of the rules: what a filled disk or an exhausted quota leaves.
		vfsStream::setQuota( 200 );

		Actions\expectDone( 'rocket_after_flush_htaccess' )->never();

		$this->assertFalse( flush_rocket_htaccess() );
	}

	/**
	 * A write cut short leaves the file as it was, not as far as it got.
	 *
	 * @return void
	 */
	public function testShouldPutBackWhatAWriteCutShortWouldHaveTaken() {
		$before = "# Someone else\n# BEGIN WP Rocket\nRules\n# END WP Rocket\n# And after\n";
		$this->given_a_file( $before );

		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			define( 'WP_ROCKET_VERSION', '3.24' );
		}

		Functions\when( 'get_bloginfo' )->justReturn( 'UTF-8' );
		Functions\when( 'get_rocket_option' )->justReturn( 0 );
		Functions\when( 'is_rocket_generate_caching_mobile_files' )->justReturn( false );
		Functions\when( 'is_multisite' )->justReturn( true );

		// Room for a fraction of the rules: what a filled disk or an exhausted quota leaves.
		vfsStream::setQuota( 200 );

		Actions\expectDone( 'rocket_after_flush_htaccess' )->never();

		$this->assertFalse( flush_rocket_htaccess() );
		$this->assertSame( $before, $this->file_contents() );
	}

	/**
	 * Writes the file this test works on.
	 *
	 * @param string $contents Contents to write.
	 *
	 * @return void
	 */
	private function given_a_file( string $contents ) {
		file_put_contents( 'vfs://home/.htaccess', $contents ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
	}

	/**
	 * Returns what the file holds now.
	 *
	 * @return string
	 */
	private function file_contents(): string {
		return (string) file_get_contents( 'vfs://home/.htaccess' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents
	}
}
