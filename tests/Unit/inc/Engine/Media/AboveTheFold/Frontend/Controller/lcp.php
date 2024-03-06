<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\Frontend\Controller;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Capabilities\Manager;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Rows\AboveTheFold as ATFRow;
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

/**
 * @covers \WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller::lcp
 *
 * @group Media
 * @group ATF
 */
class Test_lcp extends TestCase {
	private $options;
	private $query;
	private $controller;
	private $context;
	private $manager;
	private $filesystem;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->query   = $this->createPartialMock( AboveTheFold::class, [ 'get_row' ] );
		$this->context = Mockery::mock( Context::class );
		$this->manager = Mockery::mock(ManagerInterface::class);
		$this->filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		$this->controller = new Controller( $this->options, $this->query, $this->context, $this->manager, $this->filesystem );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$row = $this->createPartialMock( ATFRow::class, [ 'has_lcp' ] );

		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		$GLOBALS['wp'] = $config['wp'];

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'add_query_arg' )->returnArg( 2 );

		$this->query->method( 'get_row' )
			->with( $config['url'], $config['is_mobile'] )
			->willReturn( $config['row'] );

		$row->method('has_lcp')->willReturn($config['row_exists']);
		if ( $config['row_exists'] ) {
			$this->filesystem->shouldReceive('exists')->andReturn(true);
		}

		$this->options->shouldReceive( 'get' )
			->with( 'cache_mobile', 0 )
			->atMost()
			->once()
			->andReturn( $config['cache_mobile'] );

		$this->options->shouldReceive( 'get' )
			->with( 'do_caching_mobile_files', 0 )
			->atMost()
			->once()
			->andReturn( $config['do_caching_mobile_files'] );

		Functions\when( 'wp_is_mobile' )
			->justReturn( $config['wp_is_mobile'] );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->controller->lcp( $html ) )
		);
	}
}
