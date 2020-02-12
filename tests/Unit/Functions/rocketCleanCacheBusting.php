<?php
namespace WP_Rocket\Tests\Unit\Functions;

use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_cache_busting()
 * @group Functions
 * @group Files
 */
class Test_RocketCleanCacheBusting extends FilesystemTestCase {
	protected $rootVirtualDir = 'cache';
	protected $structure = [
		'busting' => [
			'1' => [
				'wp-content' => [
					'themes' => [
						'storefront' => [
							'assets' => [
								'js' => [
									'navigation.min-2.5.3.js'    => '',
									'navigation.min-2.5.3.js.gz' => '',
								],
							],
							'style-2.5.3.css'    => '',
							'style-2.5.3.css.gz' => '',
						],
					],
				],
			]
		]
	];

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )
			->andReturn( 'vfs://cache/busting/' );
		Functions\when( 'get_current_blog_id' )->justReturn( '1' );
	}

	public function testShouldCleanCacheBustingCSS() {
		rocket_clean_cache_busting( 'css' );

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css.gz' ) );
	}

	public function testShouldCleanCacheBustingJS() {
		rocket_clean_cache_busting( 'js' );

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
	}

	public function testShouldCleanAllCacheBusting() {
		rocket_clean_cache_busting();

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css.gz' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
	}
}
