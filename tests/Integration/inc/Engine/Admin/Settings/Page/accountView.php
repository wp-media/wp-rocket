<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\IsolateHookTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering views/settings/page-sections/account.php
 *
 * @uses \WP_Rocket\Abstract_Render::generate
 * @uses ::rocket_get_constant
 * @uses ::rocket_do_action_and_deprecated
 *
 * @group AdminOnly
 * @group SettingsPage
 */
class Test_AccountView extends TestCase {
	use IsolateHookTrait;

	public function set_up() {
		parent::set_up();

		// Real subscribers (RocketCDN, Analytics) render on this hook; keep only the test callback.
		add_action( 'rocket_account_after_license_info', [ $this, 'afterLicenseInfo' ] );
		$this->unregisterAllCallbacksExcept( 'rocket_account_after_license_info', 'afterLicenseInfo' );
		add_action( 'rocket_account_license_info', [ $this, 'licenseInfo' ] );
	}

	public function tear_down() {
		remove_action( 'rocket_account_license_info', [ $this, 'licenseInfo' ] );
		$this->restoreWpHook( 'rocket_account_after_license_info' );

		parent::tear_down();
	}

	public function licenseInfo() {
		echo '<i id="license-hook"></i>';
	}

	public function afterLicenseInfo() {
		echo '<i id="after-hook"></i>';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderExpectedStates( $config, $expected ) {
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $constant_name, $default = null ) use ( $config ) {
				if ( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' === $constant_name ) {
					return $config['white_label'];
				}

				return defined( $constant_name ) ? constant( $constant_name ) : $default;
			}
		);


		$container = apply_filters( 'rocket_container', null );
		$render    = $container->get( 'settings_render' );

		$html = $render->generate(
			'page-sections/account',
			[
				'id'            => 'account',
				'title'         => 'Account',
				'customer_data' => $config['customer_data'],
			]
		);

		foreach ( $expected['contains'] as $needle ) {
			$this->assertStringContainsString( $needle, $html );
		}

		foreach ( $expected['not_contains'] as $needle ) {
			$this->assertStringNotContainsString( $needle, $html );
		}
	}
}
