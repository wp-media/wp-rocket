<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Themify;

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

    public function set_up() {
        parent::set_up();

        $this->themify = new Themify();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {
		Filters\expectApplied('rocket_disable_rucss_setting')->with(false)->andReturn( $config['rucss_enabled'] );
		$this->assertSame($expected, $this->themify->disable_concat_on_saving_data($config['value']));
    }
}
