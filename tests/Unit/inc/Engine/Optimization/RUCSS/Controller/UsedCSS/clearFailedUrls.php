<?php

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use Brain\Monkey\Functions;
use Brain\Monkey\Actions;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::clear_failed_urls
 *
 * @group  RUCSS
 */
class Test_ClearFailedUrls extends TestCase {
	protected $options;
	protected $usedCssQuery;
	protected $api;
	protected $queue;
	protected $usedCss;
	protected $data_manager;
	protected $filesystem;

	protected function setUp(): void {
		parent::setUp();
		$this->options      = Mockery::mock( Options_Data::class );
		$this->usedCssQuery = $this->createMock( UsedCSS_Query::class );
		$this->api          = Mockery::mock( APIClient::class );
		$this->queue        = Mockery::mock( QueueInterface::class );
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->filesystem   = Mockery::mock( Filesystem::class );
		$this->context = Mockery::mock(ContextInterface::class);
		$this->optimisedContext = Mockery::mock(ContextInterface::class);
		$this->usedCss      = Mockery::mock(
			UsedCSS::class . '[is_allowed,update_last_accessed,add_url_to_the_queue]',
			[
				$this->options,
				$this->usedCssQuery,
				$this->api,
				$this->queue,
				$this->data_manager,
				$this->filesystem,
				$this->context,
				$this->optimisedContext,
			]
		);
	}

	protected function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
        $this->usedCssQuery->expects( self::once() )
			                   ->method( 'get_failed_rows' )
                               ->willReturn( $config['rows'] );

        if ( isset( $config['is_int'] ) ) {
            if ( ! $config['is_int'] ) {
                $this->usedCssQuery->expects( self::never() )
                                ->method( 'reset_job' );
            }
            else {
                foreach ( $config['rows'] as $row ) {
					Functions\when( 'home_url' )->justReturn( 'http://example.org' );

					$this->usedCss->expects()->add_url_to_the_queue($row->url, $row->is_mobile);
                }
            }

            Actions\expectDone( 'rocket_rucss_after_clearing_failed_url' )->with( $expected['failed_urls'] );
        }
        else {
            Actions\expectDone( 'rocket_rucss_after_clearing_failed_url' )->never();
        }

        $this->usedCss->clear_failed_urls();
	}
}
