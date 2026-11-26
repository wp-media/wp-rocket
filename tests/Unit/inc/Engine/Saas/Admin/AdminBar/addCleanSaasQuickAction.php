<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Saas\Admin\AdminBar;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Saas\Admin\AdminBar;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Saas\Admin\AdminBar::add_clean_saas_quick_action
 *
 * @group  RUCSS
 */
class Test_AddCleanSaasQuickAction extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$options = Mockery::mock( Options_Data::class );
		$options->shouldReceive( 'get' )->with( 'remove_unused_css', 0 )->andReturn( $config['rucss'] );

		$admin_bar = new AdminBar( $options, Mockery::mock( ContextInterface::class ), '' );

		$this->stubTranslationFunctions();
		Functions\when( 'wp_get_environment_type' )->justReturn( $config['environment'] );
		Functions\when( 'rocket_valid_key' )->justReturn( $config['valid_key'] );
		Functions\when( 'current_user_can' )->alias(
			function ( $cap ) use ( $config ) {
				return $config['caps'][ $cap ];
			}
		);
		Functions\when( 'admin_url' )->alias(
			function ( $path ) {
				return "http://example.org/wp-admin/{$path}";
			}
		);
		Functions\when( 'wp_nonce_url' )->alias(
			function ( $url, $action ) {
				return "{$url}|{$action}";
			}
		);

		$this->assertSame( $expected, $admin_bar->add_clean_saas_quick_action( [] ) );
	}
}
