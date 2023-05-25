<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeActionsSubscriber;

use WP_Rocket\Engine\Cache\PurgeActionsSubscriber;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\Purge;


use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber::on_update
 */
class Test_onUpdate extends TestCase {

	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var Purge
	 */
	protected $purge;

	/**
	 * @var PurgeActionsSubscriber
	 */
	protected $purgeactionssubscriber;

	public function set_up() {
		parent::set_up();
		$this->options = Mockery::mock(Options_Data::class);
		$this->purge = Mockery::mock(Purge::class);

		$this->purgeactionssubscriber = new PurgeActionsSubscriber($this->options, $this->purge);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config )
	{
		if($config['is_superior']) {
			Functions\expect('rocket_generate_advanced_cache_file')->never();
		} else {
			Functions\expect('rocket_generate_advanced_cache_file');
		}
		$this->purgeactionssubscriber->on_update( $config['new_version'], $config['old_version'] );
	}
}
