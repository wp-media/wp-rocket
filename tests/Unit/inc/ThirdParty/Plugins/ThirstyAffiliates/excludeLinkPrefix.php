<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\ThirstyAffiliates::exclude_link_prefix
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

		$excluded = [
			'/go/',
		];

		$this->assertSame(
			$excluded,
			$this->thirsty->exclude_link_prefix( $excluded )
		);
	}

	public function testShouldReturnOptionValueWhenNoCustom() {
		Functions\when( 'is_plugin_active' )->justReturn( true );

		$excluded = [
			'/go/',
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
			$this->thirsty->exclude_link_prefix( $excluded )
		);
	}

	public function testShouldReturnCustomOptionValueWhenCustom() {
		Functions\when( 'is_plugin_active' )->justReturn( true );

		$excluded = [
			'/go/',
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
			'/referral/',
		];

		$this->assertSame(
			$expected,
			$this->thirsty->exclude_link_prefix( $excluded )
		);
	}
}
