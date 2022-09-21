<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use wpdb;

/**
 * @covers \WP_Rocket\Engine\Preload\Activation::activate
 * @group  Preload
 */
class Test_Activate extends TestCase {
    protected $activation;
    protected $queue;
    protected $query;

    public static function setUpBeforeClass(): void {
        parent::setUpBeforeClass();

        require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
    }

    protected function setUp(): void {
        parent::setUp();

        $this->queue = Mockery::mock( Queue::class );
        $this->query = $this->createMock( Cache::class );
        $this->activation = new Activation( $this->queue, $this->query );

        $GLOBALS['wpdb'] = new wpdb();
    }

    protected function tearDown(): void {
        unset( $GLOBALS['wpdb'] );

		parent::tearDown();
    }

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected() {
        $this->queue->expects()->add_job_preload_job_load_initial_sitemap_async();

        $this->activation->activate();
    }
}

