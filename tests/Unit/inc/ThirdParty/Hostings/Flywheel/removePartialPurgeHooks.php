<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Hostings\Flywheel;

use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * @covers WP_Rocket/ThirdParty/Hostings/Flywheel::remove_partial_purge_hooks
 *
 */
class Test_removePartialPurgeHooks extends TestCase {

	/**
	 * @var Flywheel
	 */
	protected $subscriber;

	protected function set_up()
	{
		parent::set_up();
		$this->subscriber = new Flywheel();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected )
    {
		foreach ($expected['actions'] as $method => $hooks) {
			foreach ($hooks as $hook) {
				Actions\expectRemoved($hook)->with($method);
			}
		}
		foreach ($expected['filters'] as $method => $hooks) {
			foreach ($hooks as $hook) {
				Filters\expectRemoved($hook)->with($method);
			}
		}
		$this->subscriber->remove_partial_purge_hooks();
    }
}
