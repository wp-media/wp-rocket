<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SimpleCustomCss;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::update_cache_file
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_DeleteCacheFile extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/updateCacheFile.php';

	public static function set_up_before_class() {
		self::installFresh();
		parent::set_up_before_class();
	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function testShouldDeleteTheFileAndRecreateIt() {
		$filepath = 'wp-content/cache/busting/1/sccss.css';
		$content  = '.simple-custom-css { color: blue; }';

		update_option(
			'sccss_settings',
			[
				'sccss-content' => $content,
			]
		);

		$this->assertTrue( $this->filesystem->exists( $filepath ) );
		$this->assertSame( $content, $this->filesystem->get_contents( $filepath ) );
	}
}
