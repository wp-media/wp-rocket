<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Frontend\FetchSitemap;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Frontend\{FetchSitemap, SitemapParser};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\FetchSitemap::parse_sitemap
 * @group  Preload
 */
class Test_ParseSitemap extends TestCase {
	protected $sitemap_parser;
	protected $queue;
	protected $query;
	protected $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->sitemap_parser = Mockery::mock( SitemapParser::class );
		$this->queue = Mockery::mock( Queue::class );
		$this->query = $this->createMock( Cache::class );
		$this->controller = Mockery::mock( FetchSitemap::class . '[is_excluded_by_filter,is_private]', [$this->sitemap_parser, $this->queue, $this->query] )->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config ) {
		Functions\expect( 'wp_safe_remote_get' )
			->once()
			->with( $config['url'] )
			->andReturn( $config['response'] );
		Functions\expect( 'wp_remote_retrieve_response_code' )
			->once()
			->with( $config['response'] )
			->andReturn( $config['status'] );

		$this->configureRequest( $config );
		$this->configureParseSitemap( $config );

		$this->controller->parse_sitemap( $config['url'] );
	}

	protected function configureRequest( $config ) {
		if ( ! $config['request_succeed'] ) {
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

		foreach ( $config['links'] as $index => $link ) {
			$this->controller->expects()->is_private( $link )
				->once()
				->andReturn( $config['is_private'] );

			if ( $config['is_private'] ) {
				$this->controller->shouldReceive('is_excluded_by_filter')->never();
			}
			else{
				$this->controller->expects()->is_excluded_by_filter( $link )
					->once()
					->andReturn( $config['is_excluded'] );
			}
			
			if ( ! $config['is_excluded'] && ! $config['is_private'] ) {
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
