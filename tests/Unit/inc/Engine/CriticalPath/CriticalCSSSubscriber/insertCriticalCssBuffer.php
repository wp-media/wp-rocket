<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use FilesystemIterator;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 * @group  Subscribers
 * @group  CriticalCssX
 * @group  vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBufferUnit.php';
	private $critical_css;
	private $subscriber;
	private $options;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( 'cache/critical-css/' ) );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com' );
		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$this->critical_css = Mockery::mock( CriticalCSS::class, [ $this->createMock( CriticalCSSGeneration::class ) ] );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->subscriber   = new CriticalCSSSubscriber( $this->critical_css, $this->options );
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldInsertCriticalCSS( $config, $expected, $expected_html = null ) {
		$critical_css_path = $this->config['vfs_dir'] . '1/';

		$this->assertTrue( $this->filesystem->is_dir( $critical_css_path ) );

		$config['options'] = ( ! empty( $config['options'] ) ? $config['options'] : [] );

		Functions\expect( 'rocket_get_constant' )
			->with( 'DONOTROCKETOPTIMIZE' )
			->once()
			->andReturn( $config['DONOTROCKETOPTIMIZE'] );

		if ( isset( $config['DONOTASYNCCSS'] ) ) {
			Functions\expect( 'rocket_get_constant' )
			->with( 'DONOTASYNCCSS' )
			->once()
			->andReturn( $config['DONOTASYNCCSS'] );
		} else {
			Functions\expect( 'rocket_get_constant' )
				->with( 'DONOTASYNCCSS' )
				->never();
		}

		foreach ( $config['options'] as $option => $value ) {
			$this->options->shouldReceive( 'get' )
				->with( $option, $value['default'] )
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
			$this->critical_css->shouldReceive( 'get_current_page_critical_css' )->once()->andReturn( $config['get_current_page_critical_css'] );
		} else {
			$this->critical_css->shouldReceive( 'get_current_page_critical_css' )->never();
		}

		if ( isset( $config['SCRIPT_DEBUG'] ) ) {
			Functions\expect( 'rocket_get_constant' )
				->with( 'SCRIPT_DEBUG' )
				->once()
				->andReturn( $config['SCRIPT_DEBUG'] );
		} else {
			Functions\expect( 'rocket_get_constant' )
				->with( 'SCRIPT_DEBUG' )
				->never();
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

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}
}
