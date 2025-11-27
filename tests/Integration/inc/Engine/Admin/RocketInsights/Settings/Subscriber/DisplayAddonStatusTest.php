<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\RocketInsights\Settings\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\RocketInsights\Settings\Subscriber::display_addon_status
 *
 * @group License
 * @group AdminOnly
 */
class DisplayAddonStatusTest extends TestCase {

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnExpected( $config, $expected ) {
		// Set up admin user with rocket_manage_options capability.
		$admin = get_role( 'administrator' );
		if ( ! $admin->has_cap( 'rocket_manage_options' ) ) {
			$admin->add_cap( 'rocket_manage_options' );
		}
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$container = apply_filters( 'rocket_container', null ); // @phpstan-ignore-line

		$container->get( 'user' )->set_user( $config['customer_data'] );

        if ( isset( $config['date_format'] ) ) {
            update_option( 'date_format', $config['date_format'] ); // @phpstan-ignore-line
        }

		if ( isset( $config['rocket_insights_enabled'] ) ) {
			add_filter( 'rocket_rocket_insights_enabled', function() use ( $config ) {
				return $config['rocket_insights_enabled'];
			} );
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
