<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::generate_critical_css_on_activation
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::get_critical_css_path
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::process_handler
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::cancel_process
 * @uses   ::rocket_mkdir_p
 * @uses   ::rocket_get_constant
 *
 * @group  Subscribers
 * @group  CriticalCss
 * @group  vfs
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/generateCriticalCssOnActivation.php';
	protected static $transients = [
		'rocket_critical_css_generation_process_running' => null,
	];
	private static $container;
	private static $user_id;
	private $subscriber;
	private $switchedBlog = false;
	private $did_filter;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id   = $factory->user->create(
			[
				'role'          => 'adminstrator',
				'user_nicename' => 'rocket_tester',
			]
		);
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function set_up() {
		parent::set_up();

		$this->switchedBlog = false;
		$this->did_filter   = [
			'do_rocket_critical_css_generation' => 0,
		];
		$this->subscriber   = self::$container->get( 'wp_rocket.engine.criticalpath.serviceprovider.critical_css_subscriber' );
	}

	public function tear_down() {
		parent::tear_down();

		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'return_true' ] );

		if ( $this->switchedBlog ) {
			restore_current_blog();
			$this->switchedBlog = false;
		}
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldProcessNonMultisite( $values, $mobile, $expected ) {
		if ( $mobile ) {
			add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );
			add_filter( 'pre_get_rocket_option_async_css_mobile', [ $this, 'return_true' ] );
		}

		$this->assertTrue( $this->filesystem->is_dir( $this->config['vfs_dir'] . '1/' ) );

		if ( $expected ) {
			$this->filesystem->delete( 'wp-content/cache/critical-css/1/critical.css' );
		}

		// Run it.
		do_action( 'update_option_wp_rocket_settings', $values['old'], $values['new'] );

		$transient = get_transient( 'rocket_critical_css_generation_process_running' );

		if ( $expected ) {
			$this->assertSame( [ 'total', 'items' ], array_keys( $transient ) );
		} else {
			$this->assertFalse( $transient );
		}
	}

	/**
	 * @dataProvider multisiteTestData
	 * @group        Multisite
	 */
	public function testShouldProcessMultisite( $values, $site_id, $should_generate ) {
		if ( get_current_blog_id() !== $site_id ) {
			$this->createSite( $site_id );
		}

		$critical_css_path = $this->config['vfs_dir'] . "{$site_id}/";
		$this->assertSame( get_current_blog_id(), $site_id );
		$this->setCriticalCssPath( $site_id );

		if ( $should_generate ) {
			add_filter( 'do_rocket_critical_css_generation', [ $this, 'do_rocket_critical_css_generation_cb' ] );
			$this->assertFalse( $this->filesystem->is_dir( $critical_css_path ) );
		}

		$this->assertFalse( get_transient( 'rocket_critical_css_generation_process_running' ) );

		// Run it.
		$this->subscriber->generate_critical_css_on_activation( $values['old'], $values['new'] );

		$this->assertTrue( $this->filesystem->is_dir( $critical_css_path ) );

		if ( $should_generate ) {
			$this->assertEquals( 1, $this->did_filter['do_rocket_critical_css_generation'] );
			$value = get_transient( 'rocket_critical_css_generation_process_running' );
			$this->assertSame( [ 'total', 'items' ], array_keys( $value ) );
		} else {
			$this->assertEquals( 0, $this->did_filter['do_rocket_critical_css_generation'] );
			$this->assertFalse( get_transient( 'rocket_critical_css_generation_process_running' ) );
		}
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

	public function multisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['multisite'];
	}

	private function createSite( $site_id ) {
		if ( empty( get_blogaddress_by_id( $site_id ) ) ) {
			$this->factory->blog->create(
				[
					'network_id' => $site_id,
					'user_id'    => self::$user_id,
				]
			);
		}

		switch_to_blog( $site_id );
		$this->switchedBlog = true;
	}

	private function setCriticalCssPath( $site_id ) {
		$property     = $this->get_reflective_property( 'critical_css', CriticalCSSSubscriber::class );
		$critical_css = $property->getValue( $this->subscriber );

		$this->set_reflective_property(
			rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . $site_id . '/',
			'critical_css_path',
			$critical_css
		);
	}

	public function do_rocket_critical_css_generation_cb( $value ) {
		$this->did_filter['do_rocket_critical_css_generation'] ++;

		return $value;
	}
}
