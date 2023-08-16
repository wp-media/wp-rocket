<?php

use WP_Rocket\Admin\Options_Data;
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
		$this->usedCss      = Mockery::mock(
			UsedCSS::class . '[is_allowed,update_last_accessed]',
			[
				$this->options,
				$this->usedCssQuery,
				$this->api,
				$this->queue,
				$this->data_manager,
				$this->filesystem
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
				//var_dump($config['is_int']);
                foreach ( $config['rows'] as $row ) {
					$this->options->expects()
						->get( 'remove_unused_css_safelist', [] )
						->andReturn( [] );
					Functions\when( 'home_url' )->justReturn( 'http://example.org' );
					$this->api->expects()->add_to_queue( $row->url, [
						'treeshake'      => 1,
						'rucss_safelist' => [],
						'skip_attr' => [],
						'is_mobile'      => $row->is_mobile,
						'is_home'        => false,
					] )
						->andReturn( $config['add_to_queue_response']);
                    $this->usedCssQuery->expects( self::any() )
                                ->method( 'reset_job' )
                                ->with($this->anything())
                                ->will($this->returnCallback(
                                    function ( $value ) use ($row) {
                                        return $value === $row->id;
                                    }
                                 ) );
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
