<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_critical_css_content
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GetCriticalCssContent extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getCriticalCssContent.php';
	protected static $critical_css;
	protected $async_css_mobile;
	protected $cache_mobile;
    protected $fallback;
    protected $is_mobile;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container          = apply_filters( 'rocket_container', null );
		self::$critical_css = $container->get( 'critical_css' );
	}

	public function tearDown() {
		remove_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
        remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );
        remove_filter( 'pre_get_rocket_option_critical_css', [ $this, 'critical_css' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->is_mobile        = $config['wp_is_mobile'];
		$this->cache_mobile     = $config['settings']['do_caching_mobile_files'];
		$this->async_css_mobile = $config['settings']['async_css_mobile'];
		$this->fallback         = $config['settings']['critical_css'];

		add_filter( 'wp_is_mobile', [ $this, 'is_mobile' ] );
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'cache_mobile' ] );
        add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'async_css_mobile' ] );
        add_filter( 'pre_get_rocket_option_critical_css', [ $this, 'critical_css' ] );

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

		$this->assertSame(
            $expected,
            self::$critical_css->get_critical_css_content()
        );
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

	public function critical_css() {
		return $this->fallback;
	}
}
