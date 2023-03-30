<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\Purge;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Logger\Logger;

/**
 * @covers \WP_Rocket\Engine\Cache\Purge::purge_cache_reject_uri_partially
 * 
 * @group  purge_actions
 */
class Test_PurgeCacheRejectUriPartially extends FilesystemTestCase {
    protected $path_to_test_data = '/inc/Engine/Cache/Purge/purgeCacheRejectUriPartially.php';

    private $purge, $query;

    protected function setUp(): void {
		parent::setUp();

        $this->query = $this->createPartialMock(Cache::class, ['query']);
		$this->purge = new Purge( $this->filesystem, $this->query );
	}

    protected function tearDown(): void {
		parent::tearDown();
	}

    /**
	 * @dataProvider providerTestData
	 */
    public function testShouldPurgePartiallyWhenCacheRejectUriOptionIsChanged( $config, $expected ) {
        if ( ! isset( $expected['cleaned'] ) ) {
            Functions\expect( 'home_url' )->never();
            Functions\expect( 'rocket_clean_files' )->never();
        }
        else {
            if ( isset( $config['db_url_result'] ) ) {  
                $this->query->expects(self::atLeastOnce())
                ->method('query')
                ->willReturn($config['db_url_result']);
            }

            foreach ( $config['value']['cache_reject_uri'] as $path ) {
                if ( '/hello-world/' === $path ) {
                    Functions\expect( 'home_url' )
                    ->once()
                    ->with( $path )
                    ->andReturn( 'https://example.org' . $path );
                }
            }

            Functions\expect( 'rocket_clean_files' )
            ->once()
            ->with( $config['urls'] )
            ->andReturnNull();
        }

        $this->purge->purge_cache_reject_uri_partially( $config['old_value'], $config['value'] );
    }
}