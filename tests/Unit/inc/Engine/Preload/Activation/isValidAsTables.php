<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use wpdb;

/**
 * @covers \WP_Rocket\Engine\Preload\Activation::is_valid_as_tables
 * @group  Preload
 */
class Test_IsValidAsTables extends TestCase
{

	protected $activation;
	protected $queue;
	protected $query;

	private $wpdb;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/wpdb.php';
	}

	protected function setUp(): void
	{
		parent::setUp();
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->activation = new Activation($this->queue, $this->query);
		
		$GLOBALS['wpdb'] = $this->wpdb = new wpdb();
	}

	public function tear_down() {
		parent::tear_down();

		unset($GLOBALS['wpdb']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->wpdb->setTableRows( $config['found_as_tables'] );

		$this->assertSame( $expected, $this->activation->is_valid_as_tables() );
	}
}
