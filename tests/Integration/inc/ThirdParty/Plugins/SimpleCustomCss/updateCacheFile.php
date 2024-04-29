<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\SimpleCustomCss;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\SimpleCustomCss::update_cache_file
 * @group  ThirdParty
 * @group  WithSCCSS
 */
class Test_DeleteCacheFile extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/ThirdParty/Plugins/SimpleCustomCss/updateCacheFile.php';

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
