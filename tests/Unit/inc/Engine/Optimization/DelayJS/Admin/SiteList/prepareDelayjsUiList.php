<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\SiteList;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Theme;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList::prepare_delayjs_ui_list
 *
 * @group  DelayJS
 */
class SiteListTest extends TestCase {
    private $dynamic_lists_mock;
    private $options_data_mock;
    private $options_mock;
    private $theme_mock;

    protected function setUp(): void {
        parent::setUp();
        $this->dynamic_lists_mock = $this->createMock(DynamicLists::class);
        $this->options_data_mock = $this->createMock(Options_Data::class);
        $this->options_mock = $this->createMock(Options::class);
        $this->theme_mock = $this->createMock( WP_Theme::class );
    }

    /**
	 * @dataProvider configTestData
	 */
    public function testPrepareDelayjsUiList( $config, $expected ) {

        $this->dynamic_lists_mock->method( 'get_delayjs_list' )->willReturn( $config['dynamic_lists'] );

        $site_list = new SiteList( $this->dynamic_lists_mock, $this->options_data_mock, $this->options_mock );

        $this->stubTranslationFunctions();
 
        $this->theme_mock->method('get')->willReturn( 'flatsome' );
    
        // Mock the wp_get_theme function to return the theme mock
        Functions\when('wp_get_theme')->justReturn( $this->theme_mock );

        Functions\when('get_option')->justReturn( $config['active_plugins'] );
        Functions\when('is_multisite')->justReturn( false );

        $result = $site_list->prepare_delayjs_ui_list();

        $this->assertSame( $result, $expected );
    }
}