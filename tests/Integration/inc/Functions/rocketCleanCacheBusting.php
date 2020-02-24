<?php
namespace WP_Rocket\Tests\Integration\inc\Functions;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers rocket_clean_cache_busting()
 * @group Functions
 * @group AdminOnly
 */
class Test_RocketCleanCacheBusting extends FilesystemTestCase {
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

	public function testShouldCleanAllCacheBusting() {
		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_CACHE_BUSTING_PATH' )
			->andReturn( 'vfs://cache/busting/' );

		update_option( 'wp_rocket_settings', [
            'minify_css' => 0,
			'minify_js' => 0,
			'exclude_css' => [],
			'exclude_js' => [],
			'remove_query_strings' => 1,
		] );

		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/style-2.5.3.css.gz' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
		$this->assertFalse( $this->filesystem->exists( 'busting/1/wp-content/themes/storefront/assets/js/navigation.min-2.5.3.js' ) );
	}
}
