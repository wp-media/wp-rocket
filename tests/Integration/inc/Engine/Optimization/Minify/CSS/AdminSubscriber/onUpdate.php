<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::on_update
 *
 * @group AdminOnly
 * @group MinifyAdmin
 */
class Test_OnUpdate extends TestCase {
	public function set_up() {
		parent::set_up();

		remove_filter( 'wp_rocket_upgrade', 'rocket_new_upgrade' );
	}

	public function tear_down() {
		add_filter( 'wp_rocket_upgrade', 'rocket_new_upgrade' );

		parent::tear_down();
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

		do_action( 'wp_rocket_upgrade', '3.15', $config['old_version'] );
	}
}
