<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 *
 * @group  Subscribers
 * @group  CriticalCss
 * @group  vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBufferUnit.php';
	private   $critical_css;
	private   $subscriber;
	private   $options;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( 'cache/critical-css/' ) );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com' );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$this->critical_css = Mockery::mock( CriticalCSS::class, [
			Mockery::mock( CriticalCSSGeneration::class ),
			$this->filesystem,
		] );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->subscriber   = new CriticalCSSSubscriber( $this->critical_css, $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldInsertCriticalCSS( $config, $expected, $expected_html = null ) {
		$critical_css_path = $this->config['vfs_dir'] . '1/';

		$this->assertTrue( $this->filesystem->is_dir( $critical_css_path ) );

		if ( empty( $config['options'] ) ) {
			$config['options'] = [];
		}

		$this->donotrocketoptimize = $config['DONOTROCKETOPTIMIZE'];
		if ( isset( $config['DONOTASYNCCSS'] ) ) {
			$this->donotasynccss = $config['DONOTASYNCCSS'];
		} else {
			$this->options->shouldReceive( 'get' )->with( 'async_css', 0 )->never();
		}

		foreach ( $config['options'] as $name => $value ) {
			$this->options->shouldReceive( 'get' )
			              ->with( $name, $value['default'] )
			              ->andReturn( $value['value'] );
		}

		if ( isset( $config['is_rocket_post_excluded_option'] ) ) {
			Functions\expect( 'is_rocket_post_excluded_option' )
				->with( 'async_css' )
				->once()
				->andReturn( $config['is_rocket_post_excluded_option'] );
		} else {
			Functions\expect( 'is_rocket_post_excluded_option' )
				->with( 'async_css' )
				->never();
		}

		if ( isset( $config['get_current_page_critical_css'] ) ) {
			$this->critical_css
				->shouldReceive( 'get_current_page_critical_css' )
				->once()
				->andReturn( $config['get_current_page_critical_css'] );
		} else {
			$this->critical_css->shouldReceive( 'get_current_page_critical_css' )->never();
		}

		if ( isset( $config['SCRIPT_DEBUG'] ) ) {
			$this->script_debug = $config['SCRIPT_DEBUG'];
		}

		// Run it.
		$orig_html = '<html><head><title></title></head><body></body></html>';
		$html      = $this->subscriber->insert_critical_css_buffer( $orig_html );

		if ( $expected ) {
			$this->assertSame( $html, $expected_html );
		} else {
			$this->assertSame( $html, $orig_html );
		}
	}
}
