<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Frontend\FetchSitemap;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Frontend\{FetchSitemap, SitemapParser};
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Frontend\FetchSitemap::parse_sitemap
 * @group  Preload
 */
class Test_ParseSitemap extends TestCase {
	protected $sitemap_parser;
	protected $queue;
	protected $query;
	protected $logger;
	protected $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->sitemap_parser = Mockery::mock( SitemapParser::class );
		$this->queue = Mockery::mock( Queue::class );
		$this->query = $this->createMock( Cache::class );
		$this->logger = Mockery::mock( Logger::class );
		$this->controller = Mockery::mock( FetchSitemap::class . '[is_excluded_by_filter]', [$this->sitemap_parser, $this->queue, $this->query, $this->logger] )->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config ) {
		if ( $config['is_wp_error'] ) {
			$config['response'] = Mockery::mock( 'WP_Error' );
			$config['response']->expects()->get_error_message()->andReturn( $config['error_message'] );
		}

		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with( $config['url'] )
			->andReturn( $config['response'] );
		Functions\expect( 'is_wp_error' )
			->once()
			->with( $config['response'] )
			->andReturn( $config['is_wp_error'] );

		if ( $config['is_wp_error'] ) {
			Functions\expect( 'wp_remote_retrieve_response_code' )->never();
			$this->logger->expects()->error( $config['log'] )->once();

			$this->controller->parse_sitemap( $config['url'] );
			return;
		}

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->once()
			->with( $config['response'] )
			->andReturn( $config['status'] );

		if ( $config['log'] ) {
			$this->logger->expects()->error( $config['log'] )->once();
		} else {
			$this->logger->shouldNotReceive( 'error' );
		}

		$this->configureRequest( $config );
		$this->configureParseSitemap( $config );

		$this->controller->parse_sitemap( $config['url'] );
	}

	protected function configureRequest( $config ) {
		if ( 200 !== $config['status'] ) {
			return;
		}

		Functions\expect( 'wp_remote_retrieve_body' )
			->once()
			->with( $config['response'] )
			->andReturn( $config['content'] );
	}

	protected function configureParseSitemap($config) {
		if ( ! $config['request_succeed'] ) {
			return;
		}

		$this->sitemap_parser->expects()
			->set_content( $config['content'] )
			->once();
		$this->sitemap_parser->expects()->get_links()
			->once()
			->andReturn( $config['links'] );
		$this->sitemap_parser->expects()->get_children()
			->once()
			->andReturn( $config['children'] );

		foreach ( $config['links'] as $link ) {
			$this->controller->expects()->is_excluded_by_filter( $link )
					->once()
					->andReturn( $config['is_excluded'] );
			
			if ( ! $config['is_excluded'] ) {
				$this->query->expects( self::any() )->method( 'create_or_nothing' )
					->withConsecutive( ...$config['jobs'] )
					->willReturn( true );
			}
		}

		foreach ( $config['children'] as $child ) {
			$this->queue->expects()->add_job_preload_job_parse_sitemap_async( $child )
				->once();
		}
	}
}
