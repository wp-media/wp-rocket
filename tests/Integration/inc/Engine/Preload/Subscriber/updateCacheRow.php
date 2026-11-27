<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Subscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Subscriber::update_cache_row
 *
 * The scenarios using `map` register a synthetic `rocket_current_url` callback. They only prove that
 * Preload uses the filtered URL (wiring). They do NOT prove that TranslatePress / SEO Pack map slugs;
 * that is covered by the manual QA gate of the issue.
 *
 * @group Preload
 */
class Test_UpdateCacheRow extends AdminTestCase {
	protected $config;
	/**
	 * URLs received by the rocket_preload_completed action.
	 *
	 * @var string[]
	 */
	protected $completed_urls = [];

	/**
	 * Value of $wp->request before the test.
	 *
	 * @var string
	 */
	protected $original_request;

	public function set_up() {
		global $wp;

		parent::set_up();

		$this->original_request = $wp->request;
		$this->completed_urls   = [];

		// Install the preload cache table.
		self::installPreloadCacheTable();

		add_filter( 'pre_option_home', [ $this, 'home' ] );
		add_filter( 'rocket_preload_exclude_urls', [ $this, 'excluded' ] );
		add_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload' ] );
		add_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'mobile_files' ] );
		add_filter( 'rocket_current_url', [ $this, 'map_current_url' ] );
		add_action( 'rocket_preload_completed', [ $this, 'capture_completed' ] );
	}

	public function tear_down() {
		global $wp;

		$wp->request = $this->original_request;

		// Uninstall the preload cache table.
		self::uninstallPreloadCacheTable();

		remove_filter( 'pre_option_home', [ $this, 'home' ] );
		remove_filter( 'rocket_preload_exclude_urls', [ $this, 'excluded' ] );
		remove_filter( 'pre_get_rocket_option_manual_preload', [ $this, 'manual_preload' ] );
		remove_filter( 'pre_get_rocket_option_do_caching_mobile_files', [ $this, 'mobile_files' ] );
		remove_filter( 'rocket_current_url', [ $this, 'map_current_url' ] );
		remove_action( 'rocket_preload_completed', [ $this, 'capture_completed' ] );
		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		global $wp;

		$this->config = $config;

		$wp->request = $config['request'] ?? '';

		foreach ( $config['links'] as $link ) {
			self::addCache( $link );
		}

		do_action( 'rocket_after_process_buffer' );

		if ( $config['is_preloaded'] ) {
			$this->assertGreaterThan( 0, did_action( 'rocket_preload_completed' ) );
		}

		if ( isset( $expected['completed_url'] ) ) {
			$this->assertSame( [ $expected['completed_url'] ], $this->completed_urls );
		}

		foreach ( $expected['links'] as $link ) {
			$this->assertSame( $expected['exists'], self::cacheFound( $link ) );
		}

		foreach ( $expected['missing'] ?? [] as $link ) {
			$this->assertFalse( self::cacheFound( $link ) );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'updateCacheRow' );
	}

	/**
	 * Forces the home URL.
	 *
	 * @return string
	 */
	public function home() {
		return 'http://example.org';
	}

	public function excluded( $regexes ): array {
		return array_merge( $regexes, $this->config['regexes'] );
	}

	public function manual_preload() {
		return $this->config['manual_preload'];
	}

	/**
	 * Returns the mobile caching option from the config.
	 *
	 * @return bool
	 */
	public function mobile_files() {
		return $this->config['mobile_files'] ?? false;
	}

	/**
	 * Synthetic rocket_current_url callback mapping the internal URL to the translated one.
	 *
	 * @param string $url Current URL.
	 * @return string
	 */
	public function map_current_url( $url ) {
		return $this->config['map'][ $url ] ?? $url;
	}

	/**
	 * Records the URL received by rocket_preload_completed.
	 *
	 * @param string $url Preloaded URL.
	 * @return void
	 */
	public function capture_completed( $url ) {
		$this->completed_urls[] = $url;
	}
}
