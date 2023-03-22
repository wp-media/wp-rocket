<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Pagely;

use Mockery;
use PagelyCachePurge;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Pagely;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Pagely::clean_pagely
 *
 */
class Test_cleanPagely extends TestCase {

	/**
	 * @var Pagely
	 */
	protected $subscriber;

	public function set_up()
	{
		parent::set_up();
		$this->subscriber = new Pagely();
	}

    public function testShouldReturnExpected()
    {
		$pagely = Mockery::mock('overload:' . PagelyCachePurge::class);
		$pagely->expects()->purgeAll();
		$this->subscriber->clean_pagely();
    }
}
