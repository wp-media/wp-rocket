<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enqueue_admin_edit_script
 * @group  CriticalPath
 */
class Test_EnqueueAdminEditScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/enqueueAdminEditScript.php';
	protected static $mockCommonWpFunctionsInSetUp = true;

	private $beacon;
	private $options;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->beacon     = Mockery::mock( Beacon::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new AdminSubscriber(
			$this->options,
			$this->beacon,
			$this->filesystem->getUrl( 'wp-content/cache/critical-css/' ),
			$this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/views/metabox/cpcss' )
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->options->shouldReceive( 'get' )
				->with( 'async_css', 0 )
				->andReturn( $config['options']['async_css'] );

			$GLOBALS['post'] = (object) [
				'ID'          => $config['post']['ID'],
				'post_status' => $config['post']['post_status'],
			];
			Functions\when( 'get_post_meta' )->justReturn( $config['is_option_excluded'] );
		}

		if ( $expected ) {
			Functions\expect( 'rocket_get_constant' )
				->once()
				->with( 'WP_ROCKET_ASSETS_JS_URL' )
				->andReturn( $this->filesystem->getUrl( 'wp-content/plugins/wp-rocket/assets/js/' ) );
			Functions\expect( 'wp_enqueue_script' )->once();
		} else {
			Functions\expect( 'wp_enqueue_script' )->never();
		}

		$this->subscriber->enqueue_admin_edit_script( $config['page'] );
	}
}
