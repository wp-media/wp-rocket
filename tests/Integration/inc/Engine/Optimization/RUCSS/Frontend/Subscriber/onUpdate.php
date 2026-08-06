<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::on_update
 *
 * @group RUCSS
 */
class Test_OnUpdate extends TestCase {
	public function set_up() {
		parent::set_up();

		delete_option('wp_rocket_no_licence');
		delete_transient('wp_rocket_no_licence');

		// Isolate the upgrade hook to the callback under test so unrelated subscribers
		// (e.g. RocketInsights, which queries its table) don't run and emit DB errors.
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'on_update', 10 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'wp_rocket_upgrade' );

		delete_option('wp_rocket_no_licence');
		delete_transient('wp_rocket_no_licence');

		parent::tear_down();
	}
    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected ) {
		if ( $config['has_transient'] ) {
			set_transient('wp_rocket_no_licence', $config['has_transient']);
		}

        do_action( 'wp_rocket_upgrade', $config['new_version'], $config['old_version'] );

    	$this->assertSame(
			$expected['has_transient'],
			get_option( 'wp_rocket_no_licence' )
		);
	}
}
