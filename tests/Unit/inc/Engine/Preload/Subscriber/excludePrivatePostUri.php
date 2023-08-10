<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket_Mobile_Detect;
use WP_Query;

/**
 * @covers \WP_Rocket\Engine\Preload\Subscriber::exclude_private_post_uri
 *
 * @runTestsInSeparateProcesses
 *
 * @group  Preload
 */
class Test_ExcludePrivatePostUri extends TestCase
{
	protected $subscriber;

	protected $options;
	protected $controller;
	protected $query;
	protected $activation;
	protected $mobile_detect;
	protected $clear_cache;
	protected $queue;
    protected $wp_query;

    public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Query.php';
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->controller = Mockery::mock(LoadInitialSitemap::class);
		$this->query = $this->createMock(Cache::class);
		$this->activation = Mockery::mock(Activation::class);
		$this->mobile_detect = Mockery::mock(WP_Rocket_Mobile_Detect::class);
		$this->clear_cache = Mockery::mock(ClearCache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->subscriber = new Subscriber($this->options, $this->controller, $this->query, $this->activation, $this->mobile_detect, $this->clear_cache, $this->queue );
	}

    /**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected) {
        WP_Query::$have_posts = $config['have_posts'];
        WP_Query::$set_posts = $config['posts'];

		Functions\expect( 'get_post_types' )
			->once()
			->andReturn( $config['post_types'] );

        if ( ! $config['have_posts'] ) {
            Functions\expect( 'get_permalink' )->never();
        } else {
            Functions\expect( 'get_permalink' )
            ->once()
            ->andReturnValues( $config['get_permalink'] );
        }

        $this->assertSame( $expected, $this->subscriber->exclude_private_post_uri( $config['regex'], $config['url'] ) );
	}
}
