<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Themes\Divi;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\HTML;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Themes\Divi;
use WP_Theme;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Themes\Divi::remove_assets_generated
 * @uses   \WP_Rocket\ThirdParty\Themes\Divi::is_divi
 *
 * @group  ThirdParty
 */
class Test_RemoveAssetsGenerated extends TestCase
{
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
		$this->subscriber = new Divi($options_api, $this->options, $delayjs_html, $used_css);
	}


	public function testShouldReturnAsExpected() {
		Functions\expect('remove_all_actions')->with('et_dynamic_late_assets_generated');
		$this->subscriber->remove_assets_generated();
	}
}
