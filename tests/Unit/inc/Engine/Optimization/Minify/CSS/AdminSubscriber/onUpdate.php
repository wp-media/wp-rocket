<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::on_update
 *
 * @group AdminOnly
 * @group Minify
 */
class TestOnUpdate extends TestCase {
	/**
	 * @var AdminSubscriber
	 */
	protected $subscriber;

	public function setUp(): void {
		parent::setUp();

		$this->subscriber = new AdminSubscriber();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		if ( $expected ) {
			Functions\expect( 'rocket_clean_domain' );
		} else {
			Functions\expect( 'rocket_clean_domain' )->never();
		}

		$this->subscriber->on_update( '3.15', $config['old_version'] );
	}
}
