<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_current_page_critical_css
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GetCurrentPageCriticalCSS extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getCurrentPageCriticalCss.php';
	protected static $critical_css;
	protected $async_css_mobile;
	protected $is_mobile;
	protected $cache_mobile;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container          = apply_filters( 'rocket_container', null );
		self::$critical_css = $container->get( 'critical_css' );
	}

	public function tear_down() {
		remove_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected_file ) {
		$this->is_mobile        = $config['wp_is_mobile'];
		$this->cache_mobile     = $config['settings']['do_caching_mobile_files'];
		$this->async_css_mobile = $config['settings']['async_css_mobile'];

		add_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );

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

		$current_page_critical_css = self::$critical_css->get_current_page_critical_css();

		if ( ! empty( $expected_file ) ) {
			$this->assertSame(
				$this->filesystem->getUrl( $expected_file ),
				$current_page_critical_css
			);
		} else {
			$this->assertSame(
				$expected_file,
				$current_page_critical_css
			);
		}
	}

	public function async_css_mobile() {
		return $this->async_css_mobile;
	}

	public function cache_mobile() {
		return $this->cache_mobile;
	}

	public function is_mobile() {
		return $this->is_mobile;
	}
}
