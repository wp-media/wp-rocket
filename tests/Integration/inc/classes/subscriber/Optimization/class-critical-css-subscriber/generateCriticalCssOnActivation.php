<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Critical_CSS_Subscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber::generate_critical_css_on_activation
 * @group  Subscribers
 * @group  CriticalCss
 * @group  vfs
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/Optimization/class-critical-css-subscriber/generateCriticalCssOnActivation.php';
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

	public function setUp() {
		parent::setUp();

		$this->switchedBlog = false;
		$this->did_filter   = [
			'do_rocket_critical_css_generation' => 0,
		];
		$this->subscriber   = self::$container->get( 'critical_css_subscriber' );
		delete_transient( 'rocket_critical_css_generation_process_running' );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocket_critical_css_generation_process_running' );

		if ( $this->switchedBlog ) {
			restore_current_blog();
			$this->switchedBlog = false;
		}
	}

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldProcessNonMultisite( $values ) {
		$this->assertEquals( 0, Filters\applied( 'do_rocket_critical_css_generation' ) );
		Functions\expect( 'get_transient' )->with( 'rocket_critical_css_generation_process_running' )->never();

		$this->assertTrue( $this->filesystem->is_dir( $this->config['vfs_dir'] . '1/' ) );

		// Run it.
		$this->subscriber->generate_critical_css_on_activation( $values['old'], $values['new'] );
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
			$this->assertSame( [ 'generated', 'total', 'items' ], array_keys( $value ) );
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
		$property     = $this->get_reflective_property( 'critical_css', Critical_CSS_Subscriber::class );
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
