<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
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
	protected $options;
	protected function setUp(): void
	{
		parent::setUp();
		$options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$delayjs_html     = Mockery::mock( HTML::class );
		$used_css     = Mockery::mock( UsedCSS::class );
		$theme       = new WP_Theme( 'Divi', 'wp-content/themes/' );
		Functions\when( 'wp_get_theme' )->justReturn( $theme );
		$this->subscriber = new Divi($options_api, $this->options, $delayjs_html, $used_css );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->options->expects()->get('remove_unused_css', false)->andReturn($config['rucss_enabled']);
		if($config['rucss_enabled']) {
			Filters\expectAdded('et_use_dynamic_css')->with('__return_false');
		}

		$this->subscriber->disable_dynamic_css_on_rucss();
	}
}
