<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::get_subscribed_events
 *
 * @group Support
 */
class TestGetSubscribedEvents extends TestCase {
	public function testShouldNotSubscribeToTheWapClientCapabilityFilter() {
		$this->assertArrayNotHasKey( 'wap_client_capability', Subscriber::get_subscribed_events() );
	}

	public function testShouldStillRegisterTheColumnAndTheUnavailableNotice() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame( 'register_column', $events['admin_init'] );
		$this->assertSame( 'maybe_display_unavailable_notice', $events['admin_notices'] );
	}

	public function testShouldRevokeTheAppPasswordsOnDeactivation() {
		$this->assertSame( 'revoke_app_passwords', Subscriber::get_subscribed_events()['rocket_deactivation'] );
	}
}
