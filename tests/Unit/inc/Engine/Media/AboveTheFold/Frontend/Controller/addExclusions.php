<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\Frontend\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller;
use WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;

/**
 * Test class covering \WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller::add_exclusions
 *
 * @group Media
 * @group AboveTheFold
 */
class Test_addExclusions extends TestCase {
	private $options;
	private $query;
	private $controller;
	private $context;
	private $filesystem;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->query   = $this->createPartialMock( AboveTheFold::class, [ 'get_row' ] );
		$this->context = Mockery::mock( Context::class );
		$this->filesystem = Mockery::mock( WP_Filesystem_Direct::class );

		$this->controller = new Controller( $this->options, $this->query, $this->context, $this->filesystem );
	}

	protected function tearDown(): void {
		unset( $GLOBALS['wp'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $exclusions, $expected ) {
		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		$GLOBALS['wp'] = $config['wp'];

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'add_query_arg' )->returnArg( 2 );
		$this->stubWpParseUrl();

		$this->query->method( 'get_row' )
			->with( $config['url'], $config['is_mobile'] )
			->willReturn( $config['row'] );

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
			$expected,
			$this->controller->add_exclusions( $exclusions )
		);
	}
}
