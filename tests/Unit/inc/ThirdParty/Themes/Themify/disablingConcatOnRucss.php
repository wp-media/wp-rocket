<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Themify;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Themes\Themify;
use Brain\Monkey\Functions;

use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Themes\Themify::disabling_concat_on_rucss
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
		if($config['same_rucss']) {
			Functions\expect('themify_get_data')->never();
			Functions\expect('themify_set_data')->never();
		} else {
			Functions\expect('themify_get_data')->andReturn($config['value']);
			Functions\expect('themify_set_data')->with($expected['value']);
		}

		Functions\when('rocket_has_constant')->justReturn($config['has_constant']);


		$this->themify->disabling_concat_on_rucss( $config['old_configurations'], $config['new_configurations'] );

		$this->assertTrue(true);
    }
}
