<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Database;

use Brain\Monkey\Functions;
use wpdb;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Database::drop_resources_table
 *
 * @group  RUCSS
 */
class Test_DropResourcesTable extends TestCase {
	private $usedCSS;
	private $database;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

	protected function setUp(): void {
		parent::setUp();

		if ( $this->isPHP8() ) {
			return;
		}

		$GLOBALS['wpdb'] = new wpdb();

		$this->usedCSS  = $this->getMockBuilder( UsedCSS::class )
			->disableOriginalConstructor()
			->getMock();
		$this->database = new Database( $this->usedCSS );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wpdb'] );

		parent::tearDown();
	}

	public function testShouldDeleteTableAndOption() {
		if ( $this->isPHP8() ) {
			$this->assertTrue(true);
			return;
		}

		Functions\expect( 'delete_option' )
			->once()
			->with( 'wpr_rucss_resources_version' )
			->andReturn( true );

		$this->assertTrue( $this->database->drop_resources_table() );
	}

	/**
	 * Check if is PHP8.
	 *
	 * @return bool
	 */
	public function isPHP8() {
		$version = explode('.', PHP_VERSION);
		if ( $version[0] >= 8 ) {
			$this->assertTrue(true);
			return true;
		}

		return false;
	}
}
