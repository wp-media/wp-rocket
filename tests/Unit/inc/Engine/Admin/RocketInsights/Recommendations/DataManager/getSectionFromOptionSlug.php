<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\DataManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights as RocketInsightsQuery;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\MetricFormatter;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\APIClient;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Recommendations\DataManager::get_section_from_option_slug
 *
 * @group RocketInsights
 * @group Recommendations
 */
class Test_GetSectionFromOptionSlug extends TestCase {
	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Set up test fixtures.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->data_manager = new DataManager(
			Mockery::mock( APIClient::class ),
			Mockery::mock( Options_Data::class ),
			Mockery::mock( GlobalScore::class ),
			Mockery::mock( MetricFormatter::class ),
			$this->createMock( RocketInsightsQuery::class )
		);
	}

	/**
	 * Test that the page hash matching the option slug is returned.
	 *
	 * @dataProvider provideSlugs
	 *
	 * @param string $slug     Option slug.
	 * @param string $expected Expected page hash.
	 */
	public function testShouldReturnSectionForOptionSlug( string $slug, string $expected ) {
		$this->assertSame( $expected, $this->data_manager->get_section_from_option_slug( $slug ) );
	}

	/**
	 * Provides option slugs with their expected page hash.
	 *
	 * @return array
	 */
	public function provideSlugs(): array {
		return [
			'image_dimensions'             => [ 'image_dimensions', 'media' ],
			'defer_all_js'                 => [ 'defer_all_js', 'file_optimization' ],
			'delay_js'                     => [ 'delay_js', 'file_optimization' ],
			'lazyload_css_bg_img'          => [ 'lazyload_css_bg_img', 'media' ],
			'lazyload_iframes'             => [ 'lazyload_iframes', 'media' ],
			'lazyload'                     => [ 'lazyload', 'media' ],
			'minify_css'                   => [ 'minify_css', 'file_optimization' ],
			'minify_js'                    => [ 'minify_js', 'file_optimization' ],
			'manual_preload'               => [ 'manual_preload', 'preload' ],
			'auto_preload_fonts'           => [ 'auto_preload_fonts', 'media' ],
			'preload_links'                => [ 'preload_links', 'preload' ],
			'remove_unused_css'            => [ 'remove_unused_css', 'file_optimization' ],
			'host_fonts_locally'           => [ 'host_fonts_locally', 'media' ],
			'performance_monitoring'       => [ 'performance_monitoring', 'rocket_insights' ],
			'optimize_css_delivery'        => [ 'optimize_css_delivery', 'file_optimization' ],
			'delay_js_execution_safe_mode' => [ 'delay_js_execution_safe_mode', 'file_optimization' ],
			'lazyload_youtube'             => [ 'lazyload_youtube', 'media' ],
			'database_revisions'           => [ 'database_revisions', 'database' ],
			'database_auto_drafts'         => [ 'database_auto_drafts', 'database' ],
			'database_trashed_posts'       => [ 'database_trashed_posts', 'database' ],
			'database_spam_comments'       => [ 'database_spam_comments', 'database' ],
			'database_trashed_comments'    => [ 'database_trashed_comments', 'database' ],
			'database_optimize_tables'     => [ 'database_optimize_tables', 'database' ],
			'schedule_automatic_cleanup'   => [ 'schedule_automatic_cleanup', 'database' ],
			'cdn'                          => [ 'cdn', 'page_cdn' ],
			'control_heartbeat'            => [ 'control_heartbeat', 'heartbeat' ],
			'cache_logged_user'            => [ 'cache_logged_user', 'addons' ],
			'minify_concatenate_js'        => [ 'minify_concatenate_js', 'file_optimization' ],
			'database_all_transients'      => [ 'database_all_transients', 'database' ],
			'sucury_waf_cache_sync'        => [ 'sucury_waf_cache_sync', 'addons' ],
			'varnish_auto_purge'           => [ 'varnish_auto_purge', 'addons' ],
			'plugin_rocketcdn'             => [ 'plugin_rocketcdn', 'page_cdn' ],
			'plugin_imagify'               => [ 'plugin_imagify', 'imagify' ],
			'unknown slug'                 => [ 'not_a_known_option', 'dashboard' ],
		];
	}
}
