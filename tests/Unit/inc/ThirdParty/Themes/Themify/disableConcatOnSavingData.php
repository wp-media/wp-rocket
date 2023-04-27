<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Themify;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Themify;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Themify::disable_concat_on_saving_data
 */
class Test_disableConcatOnSavingData extends TestCase {

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
    public function testShouldReturnAsExpected( $config, $expected )
    {
		$this->options->expects()->get( 'rocket_disable_rucss_setting', false )->andReturn( $config['rucss_enabled'] );
		$this->assertSame($expected, $this->themify->disable_concat_on_saving_data($config['value']));
    }
}
