<?php

namespace WP_Rocket\Tests\Unit\inc\common;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_clean_cache_theme_update
 * @group Common
 * @group Purge
 */
class Test_RocketCleanCacheThemeUpdate extends TestCase {
	private $wp_upgrader;

	protected function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( '' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';

		$this->wp_upgrader = Mockery::mock( 'WP_Upgrader' );
	}

	public function testShouldBailOutWhenActionNotUpdate() {
		Functions\expect( 'wp_get_theme' )->never();
		Functions\expect( 'rocket_clean_domain' )->never();

		$this->assertNull( rocket_clean_cache_theme_update( $this->wp_upgrader, [ 'action' => 'some_action' ] ) );
		$this->assertNull( rocket_clean_cache_theme_update( $this->wp_upgrader, [ 'action' => '' ] ) );
	}

	public function testShouldBailOutWhenTypeNotTheme() {
		Functions\expect( 'wp_get_theme' )->never();
		Functions\expect( 'rocket_clean_domain' )->never();

		$this->assertNull(
			rocket_clean_cache_theme_update(
				$this->wp_upgrader,
				[
					'action' => 'update',
					'type'   => 'plugin',
				]
			)
		);
		$this->assertNull(
			rocket_clean_cache_theme_update(
				$this->wp_upgrader,
				[
					'action' => 'update',
					'type'   => '',
				]
			)
		);
	}

	public function testShouldBailOutWhenThemeNotInList() {
		$theme = Mockery::mock( 'WP_Theme' );
		$theme->shouldReceive( 'get_template' )->twice()->andReturn( 'rockettheme' );
		$theme->shouldReceive( 'get_stylesheet' )->twice()->andReturn( 'rockettheme' );
		Functions\expect( 'wp_get_theme' )->twice()->andReturn( $theme );
		Functions\expect( 'rocket_clean_domain' )->never();

		$this->assertNull(
			rocket_clean_cache_theme_update(
				$this->wp_upgrader,
				[
					'action' => 'update',
					'type'   => 'theme',
					'themes' => '',
				]
			)
		);
		$this->assertNull(
			rocket_clean_cache_theme_update(
				$this->wp_upgrader,
				[
					'action' => 'update',
					'type'   => 'theme',
					'themes' => [ 'twentynineteen', 'twentytwenty' ],
				]
			)
		);
	}

	public function testShouldInvokeCleanDomainWhenThemeUpdate() {
		$theme = Mockery::mock( 'WP_Theme' );
		$theme->shouldReceive( 'get_template' )->twice()->andReturn( 'rockettheme' );
		$theme->shouldReceive( 'get_stylesheet' )->twice()->andReturn( 'rockettheme' );
		Functions\expect( 'wp_get_theme' )->twice()->andReturn( $theme );
		Functions\expect( 'rocket_clean_domain' )->twice()->andReturnNull();

		rocket_clean_cache_theme_update(
			$this->wp_upgrader,
			[
				'action' => 'update',
				'type'   => 'theme',
				'themes' => [ 'twentynineteen', 'twentytwenty', 'rockettheme' ],
			]
		);

		rocket_clean_cache_theme_update(
			$this->wp_upgrader,
			[
				'action' => 'update',
				'type'   => 'theme',
				'themes' => [ 'rockettheme' ],
			]
		);
	}
}
