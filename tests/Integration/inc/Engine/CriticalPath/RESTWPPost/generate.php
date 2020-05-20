<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\RESTWPPost::generate
 * @group  CriticalPath
 * @group  vfs
 */
class Test_Generate extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/generate.php';
	private static $post_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
	}

	protected function doTest( $site_id, $config, $expected ) {
		if ( isset( $config['post_data'] ) ) {
			$config['post_data']['ID'] = self::$post_id;

			$post_id = wp_update_post( $config['post_data'], true );
		} else {
			$post_id = 0;
		}

		$post_type = isset( $config['post_data']['post_type'] )
			? $config['post_data']['post_type']
			: 'post';

		$post_request_response_code = ! isset( $config['generate_post_request_data']['code'] )
			? 200
			: $config['generate_post_request_data']['code'];
		$post_request_response_body = ! isset( $config['generate_post_request_data']['body'] )
			? ''
			: $config['generate_post_request_data']['body'];
		$get_request_response_body  = ! isset( $config['generate_get_request_data']['body'] )
			? ''
			: $config['generate_get_request_data']['body'];
		$request_timeout            = isset( $config['request_timeout'] )
			? $config['request_timeout']
			: false;
		$is_mobile                    = isset( $config['mobile'] )
			? $config['mobile']
			: false;
		Functions\expect( 'wp_remote_post' )
			->atMost()
			->times( 1 )
			->with(
				'https://cpcss.wp-rocket.me/api/job/',
				[
					'body' => [
						'url' => "http://example.org/?p={$post_id}",
						'mobile' => (int) $is_mobile
					],
				]
			)
			->andReturn( 'postRequest' );

		Functions\expect( 'wp_remote_get' )
			->atMost()
			->times( 1 )
			->andReturn( 'getRequest' );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->atMost()
			->times( 1 )
			->andReturn( $post_request_response_code );

		Functions\expect( 'wp_remote_retrieve_body' )
			->ordered()
			->atMost()
			->times( 1 )
			->with( 'postRequest' )
			->andReturn( $post_request_response_body )
			->andAlsoExpectIt()
			->atMost()
			->times( 1 )
			->with( 'getRequest' )
			->andReturn( $get_request_response_body );

		$file = $this->config['vfs_dir'] . "{$site_id}/posts/{$post_type}-{$post_id}.css";

		$body_param = [];
		if ( $request_timeout ) {
			$body_param = [ 'timeout' => true ];
		}
		$this->assertSame( $expected, $this->doRestRequest( 'POST', "/wp-rocket/v1/cpcss/post/{$post_id}", $body_param ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDoExpectedWhenNotMultisite( $config, $expected ) {
		if ( $config['current_user_can'] ) {
			$this->setUpUser();
		}

		$this->doTest( 1, $config, $expected );
	}

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	protected function setUpUser() {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_regenerate_critical_css' );

		$user_id = $this->factory->user->create(
			[ 'role' => 'administrator' ]
		);

		wp_set_current_user( $user_id );
	}
}
