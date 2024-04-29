<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::insert_critical_css_buffer
 * @uses   ::rocket_get_constant
 * @uses   ::is_rocket_post_excluded_option
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::get_critical_css_content
 * @uses   \WP_Rocket\Admin\Options_Data::get
 *
 * @group  Subscribers
 * @group  CriticalPath
 * @group  vfs
 */
class Test_InsertCriticalCssBuffer extends FilesystemTestCase {
	use SubscriberTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/insertCriticalCssBufferUnit.php';

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'wp_strip_all_tags' )->returnArg();

		$this->setUpTests( $this->filesystem, 1 );
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

		foreach ( $config['options'] as $name => $value ) {
			$this->options
				->shouldReceive( 'get' )
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

		if ( isset( $config['get_critical_css_content'] ) ) {
			$this->critical_css
				->shouldReceive( 'get_critical_css_content' )
				->once()
				->andReturn( $config['get_critical_css_content'] );
		} else {
			$this->critical_css->shouldReceive( 'get_critical_css_content' )->never();
		}

		if ( isset( $config['SCRIPT_DEBUG'] ) ) {
			$this->script_debug = $config['SCRIPT_DEBUG'];
		}

		// Run it.
		$orig_html = '<html><head><title></title></head><body></body></html>';
		$html      = $this->subscriber->insert_critical_css_buffer( $orig_html );

		if ( $expected ) {
			$this->assertSame( $expected_html, $html );
		} else {
			$this->assertSame( $orig_html, $html );
		}
	}
}
