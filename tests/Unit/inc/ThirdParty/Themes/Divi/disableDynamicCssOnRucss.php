<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use WP_Theme;

/**
 * @covers \WP_Rocket\ThirdParty\Divi::disable_dynamic_css_on_rucss
 *
 * @group  ThirdParty
 */
class Test_DisableDynamicCssOnRucss extends TestCase {

	protected $subscriber;

	protected function setUp(): void
	{
		parent::setUp();
		$options_api = Mockery::mock( Options::class );
		$options     = Mockery::mock( Options_Data::class );
		$delayjs_html     = Mockery::mock( HTML::class );
		$theme       = new WP_Theme( 'Divi', 'wp-content/themes/' );
		Functions\when( 'wp_get_theme' )->justReturn( $theme );
		$this->subscriber = new Divi($options_api, $options , $delayjs_html);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		Filters\expectApplied('pre_get_rocket_option_remove_unused_css')->andReturn($config['rucss_enabled']);
		if($config['rucss_enabled']) {
			Filters\expectAdded('et_use_dynamic_css')->with('__return_false');
		}

		$this->subscriber->disable_dynamic_css_on_rucss();
	}
}
