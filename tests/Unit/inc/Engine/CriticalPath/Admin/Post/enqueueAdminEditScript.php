<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Post;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CriticalPath\Admin\Post;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Post::enqueue_admin_edit_script
 * @uses   ::rocket_get_constant
 *
 * @group  CriticalPath
 * @group  CriticalPathPost
 */
class Test_EnqueueAdminEditScript extends TestCase {
	use AdminTrait;

	private $post;

	public function setUp() : void {
		parent::setUp();

		$this->setUpMocks();
		Functions\stubTranslationFunctions();

		$this->post = new Post(
			$this->options,
			$this->beacon,
			'wp-content/cache/critical-css/',
			WP_ROCKET_PLUGIN_ROOT . 'views/cpcss/'
		);
	}

	protected function tearDown(): void {
		unset( $GLOBALS['post'] );
		unset( $GLOBALS['pagenow'] );
		parent::tearDown();
	}

	public function testShouldReturnNullWhenCurrentUserCannot() {
		Functions\expect( 'current_user_can' )
			->once()
			->andReturn( false );

		$this->assertNull(
			$this->post->enqueue_admin_edit_script( 'edit.php' )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->setUpTest( $config );
		}

		$GLOBALS['pagenow'] = $config['pagenow'];

		if ( $expected ) {
			$this->assertExpected( $config );
		} else {
			$this->assertNotExpected();
		}

		Functions\expect( 'current_user_can' )
			->once()
			->andReturn( true );

		Functions\expect( 'get_post_type' )->andReturn( $config['post']->post_type );

		Functions\expect( 'is_post_type_viewable' )
			->with( $config['post']->post_type )
			->andReturn( true );

		$this->post->enqueue_admin_edit_script( $config['page'] );
	}

	private function assertExpected( $config ) {
		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'wp_rest' )
			->andReturn( 'wp_rest_nonce' );

		Functions\expect( 'rest_url' )
			->once()
			->with( "wp-rocket/v1/cpcss/post/{$config['post']->ID}" )
			->andReturn( 'http://example.org/wp-rocket/v1/cpcss/post/' . $config['post']->ID );

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'wpr-edit-cpcss-script',
				rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'wpr-cpcss.js',
				[],
				rocket_get_constant( 'WP_ROCKET_VERSION' ),
				true
			)
			->andReturnNull();

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'wpr-edit-cpcss-script',
				'rocket_cpcss',
				$config['wp_localize_script']
			)
			->andReturnNull();
	}

	private function assertNotExpected() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'rest_url' )->never();
		Functions\expect( 'wp_create_nonce' )->never();
		Functions\expect( 'wp_localize_script' )->never();
	}
}
