<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\Minify\CSS\AdminSubscriber::on_update
 *
 * @group AdminOnly
 * @group MinifyAdmin
 */
class TestOnUpdate extends TestCase {
	public function set_up() {
		parent::set_up();

		remove_filter( 'wp_rocket_upgrade', 'rocket_new_upgrade' );
		// Disable ATF optimization to prevent DB request (unrelated to the test).
		add_filter( 'rocket_above_the_fold_optimization', '__return_false' );
	}

	public function tear_down() {
		add_filter( 'wp_rocket_upgrade', 'rocket_new_upgrade', 10, 2 );
		remove_filter( 'rocket_above_the_fold_optimization', '__return_false' );

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
