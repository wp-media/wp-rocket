<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\Managers\AbstractManager;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Managers\AbstractManager::add_url_to_the_queue
 *
 * @group JobManager
 */
class Test_AddUrlToTheQueue extends TestCase {
	private $query;
	private $context;
	private $manager;

	protected function setUp(): void {
		parent::setUp();

		$this->query   = $this->createMock( UsedCSS_Query::class );
		$this->context = Mockery::mock( ContextInterface::class );
		$this->manager = new Manager(
			$this->query,
			Mockery::mock( Filesystem::class ),
			$this->context,
			Mockery::mock( Options_Data::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$url       = 'http://example.org/page';
		$lost_race = $config['is_allowed'] && ! $config['row'] && ! $config['created'];

		$this->context->shouldReceive( 'is_allowed' )->andReturn( $config['is_allowed'] );

		$this->query->expects( $this->exactly( (int) $config['is_allowed'] + (int) $lost_race ) )
			->method( 'get_row' )
			->willReturnCallback(
				function ( $row_url, $is_mobile, $use_cache = true ) use ( $url, $config ) {
					$this->assertSame( $url, $row_url );
					$this->assertFalse( $is_mobile );

					return $use_cache ? $config['row'] : $config['winner'];
				}
			);

		$this->query->expects( $this->exactly( (int) ( $config['is_allowed'] && ! $config['row'] ) ) )
			->method( 'create_new_job' )
			->with( $url, '', '', false, [] )
			->willReturn( $config['created'] );

		$this->query->expects( $this->exactly( (int) $expected['reset'] ) )->method( 'reset_job' );

		$this->assertSame( $expected['result'], $this->manager->add_url_to_the_queue( $url, false ) );
	}
}
