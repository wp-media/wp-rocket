<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates::exclude_link_prefix
 *
 * @group  ThirdParty
 */
class Test_ExcludeLinkPrefix extends TestCase {
	private $thirsty;

	public function setUp(): void {
		parent::setUp();

		$this->thirsty = new ThirstyAffiliates();
	}

	public function testShouldReturnDefaultWhenPluginDeactivated() {
		Functions\when( 'is_plugin_active' )->justReturn( false );

		$default = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
		];

		$excluded = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
		];

		$this->assertSame(
			$excluded,
			$this->thirsty->exclude_link_prefix( $excluded, $default )
		);
	}

	public function testShouldReturnOptionValueWhenNoCustom() {
		Functions\when( 'is_plugin_active' )->justReturn( true );

		$default = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
		];

		$excluded = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'ta_link_prefix', 'recommends' )
			->andReturn( 'recommends' );

		$expected = [
			'/recommends/',
		];

		$this->assertSame(
			$expected,
			$this->thirsty->exclude_link_prefix( $excluded, $default )
		);
	}

	public function testShouldReturnCustomOptionValueWhenCustom() {
		Functions\when( 'is_plugin_active' )->justReturn( true );

		$default = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
		];

		$excluded = [
			'/refer/',
			'/go/',
			'/recommend/',
			'/recommends/',
			'/out/',
		];

		Functions\expect( 'get_option' )
			->once()
			->with( 'ta_link_prefix', 'recommends' )
			->andReturn( 'custom' );

		Functions\expect( 'get_option' )
			->once()
			->with( 'ta_link_prefix_custom', 'recommends' )
			->andReturn( 'referral' );

		$expected = [
			'/out/',
			'/referral/',
		];

		$this->assertSame(
			array_values( $expected ),
			array_values( $this->thirsty->exclude_link_prefix( $excluded, $default ) )
		);
	}
}
