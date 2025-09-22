<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\PerformanceMonitoring\Settings\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\PerformanceMonitoring\SettingsSubscriber::display_addon_status
 *
 * @group License
 * @group AdminOnly
 */
class Test_DisplayAddonStatus extends TestCase {
    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected ) {
		$container = apply_filters( 'rocket_container', null );

		$container->get( 'user' )->set_user( $config['customer_data'] );

        if ( isset( $config['date_format'] ) ) {
            update_option( 'date_format', $config['date_format'] );
        }

        ob_start();
        do_action( 'rocket_dashboard_after_account_data' );
        $actual = ob_get_clean();

        $this->assertStringContainsString(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
    }
}
