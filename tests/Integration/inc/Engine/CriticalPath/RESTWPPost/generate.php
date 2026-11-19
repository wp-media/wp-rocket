<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\RESTWPPost;

use WP_Rocket\Tests\Integration\RESTVfsTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\RESTWPPost::generate
 *
 * @group CriticalPath
 * @group CriticalRest
 */
class Test_Generate extends RESTVfsTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/RESTWPPost/generate.php';

	// @phpstan-ignore-next-line
	private static $post_id;

	// @phpstan-ignore-next-line
	private $async_css_mobile;

	// @phpstan-ignore-next-line
	private $do_caching_mobile_files;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
	}

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'setAsyncCssMobileOption' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'setDoCachingMobileFilesOption' ] );

		parent::tear_down();
	}

	protected function doTest( $site_id, $config, $expected ) {
		// TODO(#8661): re-enable with HttpRequestTrait short-circuit (rebuild doTest() against current RESTWP::generate()).
		$this->markTestSkipped();
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
