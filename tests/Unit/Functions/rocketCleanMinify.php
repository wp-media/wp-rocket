<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_minify()
 * @group Functions
 * @group Files
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

		Functions\expect( 'rocket_get_constant' )
			->twice()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( 'vfs://cache/min/' );
		Functions\when( 'get_current_blog_id' )->justReturn( '1' );
	}

	public function testShouldCleanMinifiedCSS() {
		rocket_clean_minify( 'css' );

		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css.gz' ) );
	}

	public function testShouldCleanMinifiedJS() {
		rocket_clean_minify( 'js' );

		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' ) );
	}

	public function testShouldCleanAllMinified() {
		rocket_clean_minify();

		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/fa2965d41f1515951de523cecb81f85e.css.gz' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' ) );
	}
}
