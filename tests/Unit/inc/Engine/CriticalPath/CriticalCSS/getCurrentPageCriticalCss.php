<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSS;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_current_page_critical_css
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GetCurrentPageCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getCurrentPageCriticalCss.php';

	private $critical_css;
	private $critical_css_path;
	private $critical_css_generation;

	public function setUp() {
		parent::setUp();

		$this->critical_css_path       = 'wp-content/cache/critical-css/';
		$this->critical_css_generation = Mockery::mock( CriticalCSSGeneration::class );

		Functions\expect( 'rocket_get_constant' )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( $this->critical_css_path ) );
		Functions\expect( 'home_url' )->with( '/' )->andReturn( 'http://example.org/' );
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldDoExpected( $config, $expected_file, $fallback = null ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( $config['blog_id'] );

		foreach ( $config['expected_type'] as $expected_type ) {
			if ( ! empty( $expected_type['param'] ) ) {
				Functions\expect( $expected_type['type'] )
					->once()
					->with( $expected_type['param'] )
					->andReturn( $expected_type['return'] );
			} else {
				Functions\expect( $expected_type['type'] )
					->once()
					->andReturn( $expected_type['return'] );
			}
		}

		foreach ( $config['excluded_type'] as $excluded_type ) {
			Functions\expect( $excluded_type )->never();
		}

		$this->critical_css            = new CriticalCSS( $this->critical_css_generation, $this->filesystem );
		$get_current_page_critical_css = $this->critical_css->get_current_page_critical_css();

		if ( ! empty( $expected_file ) ) {
			$this->assertSame( $this->filesystem->getUrl( $expected_file ), $get_current_page_critical_css );
		}
		if ( isset( $fallback ) ) {
			$this->assertSame( $fallback, $get_current_page_critical_css );
		}
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}
}
