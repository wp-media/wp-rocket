<?php
namespace WP_Rocket\Tests\Integration\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_minify()
 * @group Functions
 * @group AdminOnly
 */
class Test_RocketCleanMinify extends FilesystemTestCase {
	protected $structure = [
		'min' => [
			'1' => [
				'5c795b0e3a1884eec34a989485f863ff.js'     => '',
				'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
				'fa2965d41f1515951de523cecb81f85e.css'    => '',
				'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
			],
		],
	];

	public function setUp() {
		parent::setUp();

		add_option( 'wp_rocket_settings', [
			'minify_css' => 0,
			'minify_js' => 0,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );
	}

	public function tearDown() {
		delete_option( 'wp_rocket_settings' );

		parent::tearDown();
	}

	public function testShouldCleanMinifiedCSS() {
		Functions\expect( 'rocket_get_constant' )
			->twice()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'vfs://cache/min/' );

		update_option( 'wp_rocket_settings', [
			'minify_css' => 1,
			'minify_js' => 0,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );

		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css.gz' ) );
	}

	public function testShouldCleanMinifiedJS() {
		Functions\expect( 'rocket_get_constant' )
			->twice()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'vfs://cache/min/' );

		update_option( 'wp_rocket_settings', [
			'minify_css' => 0,
			'minify_js' => 1,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );

		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' ) );
	}

	public function testShouldCleanAllMinified() {
		Functions\expect( 'rocket_get_constant' )
			->times(4)
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'vfs://cache/min/' );

		update_option( 'wp_rocket_settings', [
			'minify_css' => 1,
			'minify_js' => 1,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 0,
		] );

		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css.gz' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' ) );
	}
}
