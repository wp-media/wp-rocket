<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\PreconnectExternalDomains\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreconnectExternalDomains\Admin\Subscriber::maybe_clear_preconnect_domains
 *
 * @group PreconnectExternalDomains
 * @group Media
 */
class MaybeClearPreconnectDomains extends TestCase {
	use DBTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::installPreconnectExternalDomainsTable();
	}

	public static function tear_down_after_class() {
		self::uninstallPreconnectDomainsTable();

		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', 'maybe_clear_preconnect_domains', 12 );
	}


	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'update_option_wp_rocket_settings' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		// Add rows to the database using DBTrait.
		foreach ( $config['rows'] as $row ) {
			self::addPreconnectExternalDomains( $row );
		}

		// Trigger the action to test the logic.
		 do_action( 'update_option_wp_rocket_settings', $config['old'], $config['new'] );

		// Assert the table state matches the expected row count.
		$actual_row_count = count( apply_filters( 'rocket_container', null )
			->get( 'preconnect_external_domains_query' )
			->query( [] ) );

		$this->assertSame( $expected['row_count'], $actual_row_count );
	}
}
