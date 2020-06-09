<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Purge;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Cache\Purge;

/**
 * @covers \WP_Rocket\Engine\Cache\Purge:purge_dates_archives
 * @group  purge_actions
 */
class Test_PurgeDatesArchives extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeDatesArchives.php';

	public function setUp() {
		parent::setUp();

		$this->purge = new Purge( $this->filesystem );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCleanCache( $post, $cleaned ) {
		$post['ID'] = 1;

		$this->purge->purge_dates_archives( (object) $post );
   }
}
