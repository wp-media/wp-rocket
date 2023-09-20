<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Post;

use Brain\Monkey\Functions;
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
	private $post;

	public function setUp() : void {
		parent::setUp();

		$this->setUpMocks();
		Functions\stubTranslationFunctions();

		$this->post = Mockery::mock( Post::class . '[generate]', [
				$this->options,
				$this->beacon,
				'wp-content/cache/critical-css/',
				WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/',
			]
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['post'] );
		parent::tearDown();
	}

	public function testShouldReturnNullWhenCurrentUserCannot() {
		Functions\expect( 'current_user_can' )
			->once()
			->andReturn( false );

		$this->assertNull(
			$this->post->cpcss_actions()
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDisplayCPCSSSActions( $config, $expected ) {
		$this->setUpTest( $config );

		Functions\expect( 'current_user_can' )
			->once()
			->andReturn( true );

		$this->beacon->shouldReceive( 'get_suggest' )
		             ->once()
		             ->andReturn( $expected['data']['beacon'] );

		$this->post->shouldReceive( 'generate' )
				   ->with( 'metabox/generate', $expected['data'] )
				   ->andReturn( '' );

		Functions\expect( 'get_post_type' )
			->once()
			->andReturn( $config['post']->post_type );

		Functions\expect( 'is_post_type_viewable' )
			->once()
			->with( $config['post']->post_type )
			->andReturn( true );

		ob_start();
		$this->post->cpcss_actions();
		ob_get_clean();
	}
}
