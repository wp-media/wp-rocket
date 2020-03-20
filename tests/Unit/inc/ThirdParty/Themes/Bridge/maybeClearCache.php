<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Bridge;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Bridge;

/**
 * @covers WP_Rocket\ThirdParty\Themes\Bridge::maybe_clear_cache
 * @group Bridge
 * @group ThirdParty
 */
class Test_MaybeClearCache extends TestCase {
	private $bridge;
	private $options;

	public function setUp() {
		parent::setUp();

		$this->options = $this->createMock( Options_Data::class );
		$this->bridge  = new Bridge( $this->options );
	}

	public function neverDataProvider() {
		return $this->getTestData( __DIR__, 'no-clean' );
	}

	/**
	 * @dataProvider neverDataProvider
	 */
	public function testShouldDoNothingWhenSettingsDontMatch( $old_value, $value, $map ) {
		$this->options->method( 'get' )->will( $this->returnValueMap( $map ) );

        Functions\expect( 'rocket_clean_domain' )
			->never();
		Functions\expect( 'rocket_clean_minify' )
			->never();

		$this->bridge->maybe_clear_cache( $old_value, $value );
	}

	public function cleanDataProvider() {
		return $this->getTestData( __DIR__, 'clean' );
	}

	/**
	 * @dataProvider cleanDataProvider
	 */
	public function testShouldCleanCacheWhenSettingsMatch( $old_value, $value, $map ) {
		$this->options->method( 'get' )->will( $this->returnValueMap( $map ) );

        Functions\expect( 'rocket_clean_domain' )
			->once();
		Functions\expect( 'rocket_clean_minify' )
			->once();

		$this->bridge->maybe_clear_cache( $old_value, $value );
	}
}
