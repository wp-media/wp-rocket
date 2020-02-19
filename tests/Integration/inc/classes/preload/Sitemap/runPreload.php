<?php
namespace WP_Rocket\Tests\Integration\inc\classes\preload\Sitemap;

use Brain\Monkey\Actions;
use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Tests\Integration\Fixtures\Preload\Process_Wrapper;
use WP_Rocket\Preload\Sitemap;

/**
 * @covers \WP_Rocket\Preload\Sitemap::run_preload
 * @group Preload
 */
class Test_runPreload extends TestCase {
	protected $site_url           = 'https://smashingcoding.com';
	protected $identifier         = 'rocket_preload';
	protected $option_hook_prefix = 'pre_get_rocket_option_';
	protected $preloadErrorsTransient;
	protected $preloadRunningTransient;
	protected $process;
	protected $post_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/Preload/Process_Wrapper.php';
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'site_url', [ $this, 'setSiteUrl' ] );

		$this->preloadErrorsTransient  = get_transient( 'rocket_preload_errors' );
		$this->preloadRunningTransient = get_transient( 'rocket_preload_running' );
		$this->process                 = new Process_Wrapper();
	}

	public function setSiteUrl() {
		return $this->site_url;
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'site_url', [ $this, 'setSiteUrl' ] );

		if ( $this->process ) {
			// Added by \WP_Async_Request.
			remove_action( 'wp_ajax_' . $this->identifier, [ $this->process, 'maybe_handle' ] );
			remove_action( 'wp_ajax_nopriv_' . $this->identifier, [ $this->process, 'maybe_handle' ] );
			// Added by \WP_Background_Process.
			remove_action( $this->identifier . '_cron', [ $this->process, 'handle_cron_healthcheck' ] );
			remove_filter( 'cron_schedules', [ $this->process, 'schedule_cron_healthcheck' ] );
		}

		foreach ( [ 'manual_preload', 'cache_mobile', 'do_caching_mobile_files' ] as $option ) {
			remove_filter( $this->option_hook_prefix . $option, [ $this, 'return_0' ] );
			remove_filter( $this->option_hook_prefix . $option, [ $this, 'return_1' ] );
		}

		remove_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );
		remove_filter( $this->option_hook_prefix . 'cache_query_strings', [ $this, 'return_cache_query_strings' ] );

		set_transient( 'rocket_preload_errors', $this->preloadErrorsTransient );
		set_transient( 'rocket_preload_running', $this->preloadRunningTransient );

		$this->preloadErrorsTransient  = null;
		$this->preloadRunningTransient = null;
		$this->process                 = null;
		$this->post_id                 = null;
	}

	public function testShouldNotPreloadWhenNoUrls() {
		Actions\expectDone( 'before_run_rocket_sitemap_preload' )->never();

		// No URLs.
		( new Sitemap( $this->process ) )->run_preload( [] );
	}

	public function testShouldPreloadSitemapsWhenValidUrls() {
		$sitemaps = [
			"{$this->site_url}/mobile-preload-sitemap.xml",
			"{$this->site_url}/mobile-preload-sitemap-mobile.xml",
		];

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );

		delete_transient( 'rocket_preload_errors' );

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/", $queue );
		$this->assertContains( "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => true ], $queue );
		$this->assertCount( 5, $queue );
	}

	public function testShouldPreloadFallbackUrlsWhenInvalidSitemap() {
		$sitemaps = [
			"{$this->site_url}/mobile-preload-sitemap.xml",
			"{$this->site_url}/mobile-preload-sitemap-that-does-not-exist.xml",
		];

		$this->post_id = wp_insert_post(
			[
				'post_title'   => 'Hoy',
				'post_content' => 'Hello World',
				'post_type'    => 'page',
				'post_status'  => 'publish',
			]
		);

		if ( method_exists( $this, 'assertIsInt' ) ) {
			$this->assertIsInt( $this->post_id );
		} else {
			// Deprecated in phpunit 8.
			$this->assertInternalType( 'int', $this->post_id );
		}

		add_filter( 'page_link', [ $this, 'change_page_link' ], 10, 3 );

		$permalink = get_permalink( $this->post_id );

		$this->assertNotFalse( $permalink );

		add_filter( $this->option_hook_prefix . 'manual_preload', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_mobile', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'do_caching_mobile_files', [ $this, 'return_1' ] );
		add_filter( $this->option_hook_prefix . 'cache_reject_uri', [ $this, 'return_empty_array' ] );

		delete_transient( 'rocket_preload_errors' );

		( new Sitemap( $this->process ) )->run_preload( $sitemaps );

		$key = $this->process->getGeneratedKey();

		$this->assertNotNull( $key );

		$queue = get_site_option( $key );
		delete_site_option( $key );

		$this->assertContains( "{$this->site_url}/mobile-preload-homepage/", $queue );
		$this->assertContains( "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/mobile-preload-homepage/", 'mobile' => true ], $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/", 'mobile' => true ], $queue );
		$this->assertContains( "{$this->site_url}/category/mobile-preload/", $queue );
		$this->assertContains( [ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => true ], $queue );
		$this->assertCount( 6, $queue );

		wp_delete_post( $this->post_id, true );

		remove_filter( 'page_link', [ $this, 'change_page_link' ] );
	}

	public function change_page_link( $link, $post_id, $sample ) {
		if ( $sample || $post_id !== $this->post_id ) {
			return $link;
		}
		return 'https://smashingcoding.com/category/mobile-preload/';
	}
}
