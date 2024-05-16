<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\RESTWPPost::generate
 *
 * @group CriticalPath
 * @group CriticalRest
 */
class Test_Generate extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/generate.php';
	private static $post_id;

	private $async_css_mobile;
	private $do_caching_mobile_files;

	public static function wpSetUpBeforeClass( $factory ) {
		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		self::$post_id = $factory->post->create();

		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function set_up() {
		parent::set_up();

		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		// Re-enable ATF optimization.
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setAsyncCssMobileOption' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'setDoCachingMobileFilesOption' ] );

		parent::tear_down();
	}

	protected function doTest( $site_id, $config, $expected ) {
		$orig_post_id = isset( $config['post_data']['ID'] )
			? $config['post_data']['ID']
			: 0;

		if ( isset( $config['post_data'] ) ) {
			$config['post_data']['ID'] = self::$post_id;

			$post_id = wp_update_post( $config['post_data'], true );
		} else {
			$post_id = 0;
		}

		$post_type = isset( $config['post_data']['post_type'] )
			? $config['post_data']['post_type']
			: 'post';

		$post_title = isset( $config['post_data']['post_title'] )
			? $config['post_data']['post_title']
			: '';

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
		$no_fontface                    = isset( $config['no_fontface'] )
			? $config['no_fontface']
			: false;
		$async_css_mobile             = isset( $config['async_css_mobile'] )
			? $config['async_css_mobile']
			: 0;
		$do_caching_mobile_files      = isset( $config['do_caching_mobile_files'] )
			? $config['do_caching_mobile_files']
			: 0;

		Functions\expect( 'wp_remote_post' )
			->atMost()
			->times( 1 )
			->with(
				'https://cpcss.wp-rocket.me/api/job/',
				[
					'body' => [
						'url'        => "http://example.org/?p={$post_id}",
						'mobile'     => (int) $is_mobile,
						'nofontface' => $no_fontface,
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

		$this->async_css_mobile = $async_css_mobile;
		add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setAsyncCssMobileOption' ] );

		$this->do_caching_mobile_files = $do_caching_mobile_files;
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'setDoCachingMobileFilesOption' ] );

		$file = $this->config['vfs_dir'] . "cache/critical-css/{$site_id}/posts/{$post_type}-{$post_id}" . ( $is_mobile ? '-mobile' : '' ). ".css";

		$body_param              = [];
		$body_param['is_mobile'] = $is_mobile;
		if ( $request_timeout ) {
			$body_param['timeout'] = true;
		}

		$expected['message'] = str_replace( $orig_post_id, $post_id, $expected['message'] );

		$cache_file_path = $this->filesystem->getUrl( "{$this->config['vfs_dir']}cache/wp-rocket/example.org/{$post_title}/index.html" );
		if ( $expected['success'] ) {
			$this->assertTrue( $this->filesystem->exists( $cache_file_path ) );
		}

		$this->assertSame( $expected, $this->doRestRequest( 'POST', "/wp-rocket/v1/cpcss/post/{$post_id}", $body_param ) );
		$this->assertSame( $config['cpcss_exists_after'], $this->filesystem->exists( $file ) );

		if ( $expected['success'] ) {
			$this->assertFalse( $this->filesystem->exists( $cache_file_path ) );
		}
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldDoExpectedWhenNotMultisite( $config, $expected ) {
		if ( isset( $config['current_user_can'] ) && $config['current_user_can'] ) {
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

	public function setAsyncCssMobileOption() {
		return $this->async_css_mobile;
	}

	public function setDoCachingMobileFilesOption() {
		return $this->do_caching_mobile_files;
	}
}
