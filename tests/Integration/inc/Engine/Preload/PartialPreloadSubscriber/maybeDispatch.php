<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\PartialPreloadSubscriber;

use WP_Rocket\Tests\Fixtures\inc\Engine\Preload\PartialProcess_Wrapper;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\PartialPreloadSubscriber::maybe_dispatch
 * @group  Preload
 */
class Test_MaybeDispatch extends TestCase {
	private static $post_id;
	private $subscriber;
	private $process;
	private $process_wrapper;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$post_id = $factory->post->create();
	}

	public function setUp() : void {
		parent::setUp();

		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'return_true' ] );
		add_filter( 'pre_get_rocket_option_cache_mobile', [ $this, 'return_true' ] );
		add_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );
		remove_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

		$container             = apply_filters( 'rocket_container', null );
		$this->subscriber      = $container->get( 'partial_preload_subscriber' );
		$process_ref           = $this->get_reflective_property( 'partial_preload', $this->subscriber );
		$this->process         = $process_ref->getValue( $this->subscriber );
		$this->process_wrapper = new PartialProcess_Wrapper();

		$process_ref->setValue( $this->subscriber, $this->process_wrapper );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_cache_mobile', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_false' ] );
		remove_filter( 'pre_option_permalink_structure', [ $this, 'permalink_structure_filter' ] );
		add_action( 'shutdown', 'wp_ob_end_flush_all', 1 );

		$this->set_reflective_property( $this->process, 'partial_preload', $this->subscriber );

		$this->subscriber      = null;
		$this->process         = null;
		$this->process_wrapper = null;
	}

	public function testShouldDispatchWhenUrlsAndNoMobilePreload() {
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_false' ] );

		$post       = get_post( self::$post_id );
		$purge_urls = rocket_get_purge_urls( self::$post_id, $post );

		do_action( 'after_rocket_clean_post', $post, $purge_urls, 'en' );
		do_action( 'shutdown' );

		$key = $this->process_wrapper->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue    = get_site_option( $key );
		$post_url = get_permalink( $post );
		$home_url = get_rocket_i18n_home_url( 'en' );
		delete_site_option( $key );

		// Only desktop items.
		$this->assertContains( $post_url, $queue );
		$this->assertNotContains( [ 'url' => $post_url, 'mobile' => true ], $queue );
		$this->assertContains( $home_url, $queue );
		$this->assertNotContains( [ 'url' => $home_url, 'mobile' => true ], $queue );
	}

	public function testShouldDispatchWhenUrlsAndMobilePreload() {
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'return_true' ] );

		$post       = get_post( self::$post_id );
		$purge_urls = rocket_get_purge_urls( self::$post_id, $post );

		do_action( 'after_rocket_clean_post', $post, $purge_urls, 'en' );
		do_action( 'shutdown' );

		$key = $this->process_wrapper->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue    = get_site_option( $key );
		$post_url = get_permalink( $post );
		$home_url = get_rocket_i18n_home_url( 'en' );
		delete_site_option( $key );

		// Desktop and mobile items.
		$this->assertContains( $post_url, $queue );
		$this->assertContains( [ 'url' => $post_url, 'mobile' => true ], $queue );
		$this->assertContains( $home_url, $queue );
		$this->assertContains( [ 'url' => $home_url, 'mobile' => true ], $queue );
	}

	public function permalink_structure_filter() {
		return '/%postname%/';
	}
}
