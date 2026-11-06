<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSS;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Mockery;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSS::get_exclude_async_css
 *
 * @group  CriticalPath
 */
class Test_GetExcludeAsyncCss extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSS/getExcludeAsyncCss.php';

	private $config_data = [];

	public function tear_down()
	{
		remove_filter( 'rocket_exclude_async_css', [$this, 'setExcludeFiles'] );
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGetExcludedFiles( $config, $expected ) {
		$this->config_data = $config;

		add_filter( 'rocket_exclude_async_css', [$this, 'setExcludeFiles'] );

		$critical_css = new CriticalCSS(
			Mockery::mock( CriticalCSSGeneration::class ),
			Mockery::mock( Options_Data::class ),
			$this->filesystem
		);

		$actual = array_values( $critical_css->get_exclude_async_css() );
		$this->assertEquals($expected, $actual);
	}

	public function setExcludeFiles() {
		return $this->config_data;
	}

}
