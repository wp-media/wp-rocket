<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\I18n\WPML;

use WP_Rocket\ThirdParty\Plugins\I18n\WPML;
use Mockery;
use WP_Filesystem_Direct;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\I18n\WPML::on_change_directory_for_default_language_clean_cache
 */
class Test_onChangeDirectoryForDefaultLanguageCleanCache extends TestCase {

    /**
    * @var WP_Filesystem_Direct
    */
    protected $filesystem;

    /**
    * @var WPML
    */
    protected $wpml;

    public function set_up() {
        parent::set_up();
        $this->filesystem = Mockery::mock(WP_Filesystem_Direct::class);

        $this->wpml = new WPML($this->filesystem);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		if($config['should_clean']) {
			Functions\expect('rocket_clean_domain');
		} else {
			Functions\expect('rocket_clean_domain')->never();
		}
        $this->assertSame($expected, $this->wpml->on_change_directory_for_default_language_clean_cache($config['new'], $config['old']));

    }
}
