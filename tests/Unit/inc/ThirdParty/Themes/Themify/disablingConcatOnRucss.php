<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Themify;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Themify;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Themify::disabling_concat_on_rucss
 */
class Test_disablingConcatOnRucss extends TestCase {

    /**
     * @var Themify
     */
    protected $themify;

	protected $options;

	public function set_up() {
        parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);

        $this->themify = new Themify($this->options);
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		Functions\expect('themify_get_data')->andReturn($config['value']);
		$this->options->expects()->get('rocket_disable_rucss_setting', false)->andReturn( $config['rucss_enabled'] );

		Functions\when('rocket_has_constant')->justReturn($config['has_constant']);

		if( $config['rucss_enabled'] && $config['need_add'] ) {
			Functions\expect('themify_set_data')->with($expected['value']);
		}

		$this->themify->disabling_concat_on_rucss();

		$this->assertTrue(true);
    }
}
