<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use ReflectionClass;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::on_upgrade
 * in the context of admin notices rendering on the plugins screen after an upgrade.
 *
 * Verifies that firing wp_rocket_upgrade syncs previous_version into the in-memory
 * Options_Data instance so that any notice depending on previous_version reads the
 * correct value on the same request.
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_MaybeDisplayRocketcdnNotice extends TestCase {

	/**
	 * Original user ID.
	 *
	 * @var int
	 */
	private $original_user_id;

	/**
	 * NoticesSubscriber instance retrieved from the container.
	 *
	 * @var NoticesSubscriber
	 */
	private $notices_subscriber;

	/**
	 * Options_Data instance from the NoticesSubscriber (via Reflection).
	 *
	 * @var Options_Data
	 */
	private $subscriber_options;

	/**
	 * Sets up the test case.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->original_user_id   = get_current_user_id();
		$container                = apply_filters( 'rocket_container', null );
		$this->notices_subscriber = $container->get( 'rocketcdn_notices_subscriber' );

		// Access the private $options property via Reflection to verify on_upgrade updates it.
		$reflection = new ReflectionClass( NoticesSubscriber::class );
		$property   = $reflection->getProperty( 'options' );
		$property->setAccessible( true );
		$this->subscriber_options = $property->getValue( $this->notices_subscriber );
	}

	/**
	 * Tears down the test case.
	 *
	 * @return void
	 */
	public function tear_down() {
		wp_set_current_user( $this->original_user_id );

		parent::tear_down();
	}

	/**
	 * Tests that on_upgrade syncs previous_version into Options_Data.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration data.
	 * @param array $expected Expected test results.
	 * @return void
	 */
	public function testShouldSyncPreviousVersionOnUpgrade( $config, $expected ) {
		// Simulate pre-upgrade state: in-memory previous_version starts at initial value.
		$this->subscriber_options->set( 'previous_version', $config['initial_previous_version'] );

		// Call on_upgrade directly, simulating what wp_rocket_upgrade fires on admin_init.
		$this->notices_subscriber->on_upgrade( $config['new_version'], $config['old_version'] );

		// Verify in-memory Options_Data now holds the old version.
		$this->assertSame(
			$expected['previous_version'],
			$this->subscriber_options->get( 'previous_version', '' )
		);
	}

	/**
	 * Verifies on_upgrade correctly handles a first-ever upgrade scenario.
	 *
	 * Simulates previous_version starting as empty string (user upgrading for the
	 * first time from a version that never set previous_version in Options_Data).
	 *
	 * @return void
	 */
	public function testShouldSyncPreviousVersionWhenUpgradingFromFirstTimeWithEmptyPreviousVersion() {
		// Initial state: no previous_version set (first-ever upgrade scenario).
		$this->subscriber_options->set( 'previous_version', '' );

		// Call on_upgrade directly, simulating what wp_rocket_upgrade fires on admin_init.
		$this->notices_subscriber->on_upgrade( '3.22.0', '3.21.3' );

		// After on_upgrade fires, in-memory previous_version must equal the old version.
		$this->assertSame( '3.21.3', $this->subscriber_options->get( 'previous_version', '' ) );
	}
}
