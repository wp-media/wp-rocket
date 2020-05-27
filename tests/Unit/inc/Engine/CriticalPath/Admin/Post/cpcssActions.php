<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Post;

use Mockery;
use WP_Rocket\Engine\CriticalPath\Admin\Post;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::cpcss_actions
 * @uses   ::rocket_direct_filesystem
 *
 * @group  CriticalPath
 * @group  CriticalPathPost
 */
class Test_CpcssActions extends FilesystemTestCase {
	use AdminTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/Admin/Post/cpcssActions.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $post;

	public function setUp() {
		parent::setUp();

		$this->setUpMocks();

		$this->post = Mockery::mock( Post::class . '[generate]', [
				$this->options,
				$this->beacon,
				'wp-content/cache/critical-css/',
				WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/',
			]
		);
	}

	protected function tearDown() {
		unset( $GLOBALS['post'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCPCSSSActions( $config, $expected ) {
		$this->setUpTest( $config );

		$this->beacon->shouldReceive( 'get_suggest' )
		             ->once()
		             ->andReturn( $expected['data']['beacon'] );

		$this->post->shouldReceive( 'generate' )
				   ->with( 'metabox/generate', $expected['data'] )
				   ->andReturn( '' );

		ob_start();
		$this->post->cpcss_actions();
		ob_get_clean();
	}
}
