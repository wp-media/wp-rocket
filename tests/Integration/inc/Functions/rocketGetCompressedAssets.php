<?php
namespace WP_Rocket\Tests\Integration\inc\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_get_compressed_assets_rules
 * @group Functions
 * @group Htaccess
 */
class Test_RocketGetCompressedAssetsRules extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/functions/rocketGetCompressedAssets.php';

	public function setUp() {
		parent::setUp();

		global $is_apache;

		$is_apache = true;
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldContainHtaccessRules( $expected ) {
		Functions\expect( 'get_home_path' )
			->once()
			->andReturn( $this->filesystem->getUrl( 'public/' ) );

		flush_rocket_htaccess();

		$this->assertContains(
			$this->format_htaccess( $expected ),
			$this->format_htaccess( $this->filesystem->get_contents( '.htaccess' ) )
		);
	}

	private function format_htaccess( $string ) {
		$string = trim( $string );

		return preg_replace( '/^\s*/m', '', $string );
	}
}
