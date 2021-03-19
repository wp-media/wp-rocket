<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Savvii;

use Brain\Monkey\Functions;
use WP_Rocket\ThirdParty\Hostings\Savvii;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Savvii::clear_cache_after_savvii
 * @group Savvii
 * @group ThirdParty
 */
class Test_ClearCacheAfterSavvii extends TestCase {
	public static function setUpBeforeClass() : void {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Savvii/CacheFlusherPlugin.php';
	}

	public function tearDown() {
		parent::tearDown();

		unset( $_REQUEST['warpdrive_flush_now'] );
		unset( $_REQUEST['warpdrive_domainflush_now'] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$savvii = new Savvii();

		if ( isset( $config['warpdrive_flush_now'] ) ) {
			$_REQUEST['warpdrive_flush_now'] = true;

			Functions\expect( 'check_admin_referer' )
				->once()
				->with( 'warpdrive_flush_now' )
				->andReturn( true );
		} else {
			Functions\expect( 'check_admin_referer' )
				->never();
		}

		if ( isset( $config['warpdrive_domainflush_now'] ) ) {
			$_REQUEST['warpdrive_domainflush_now'] = true;

			Functions\expect( 'check_admin_referer' )
				->once()
				->with( 'warpdrive_domainflush_now' )
				->andReturn( true );
		} else {
			Functions\expect( 'check_admin_referer' )
				->never();
		}

		if ( $expected ) {
			Functions\expect( 'rocket_clean_domain' )->once();
			Functions\expect( 'run_rocket_bot' )->once();
			Functions\expect( 'run_rocket_sitemap_preload' )->once();
		} else {
			Functions\expect( 'rocket_clean_domain' )->never();
			Functions\expect( 'run_rocket_bot' )->never();
			Functions\expect( 'run_rocket_sitemap_preload' )->never();
		}

		$savvii->clear_cache_after_savvii();
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'clearCacheAfterSavvii' );
	}
}
