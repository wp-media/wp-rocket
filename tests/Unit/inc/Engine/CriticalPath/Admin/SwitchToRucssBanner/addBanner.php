<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\SwitchToRucssBanner;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\Admin\SwitchToRucssBanner;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\SwitchToRucssBanner::add_banner
 *
 * @group CriticalPath
 */
class Test_AddBanner extends TestCase {
	private $options;
	private $user;
	private $banner;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->user    = Mockery::mock( User::class );
		$this->banner  = Mockery::mock( SwitchToRucssBanner::class . '[generate]', [ $this->options, $this->user, 'views' ] );

		$this->stubTranslationFunctions();
		$this->stubEscapeFunctions();
	}

	public function testShouldBeHookedOnTheLicenseFilterWithTheLowestPriority() {
		$this->assertSame(
			[ 'rocket_dashboard_sidebar_license' => [ 'add_banner', 14 ] ],
			SwitchToRucssBanner::get_subscribed_events()
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $config ) {
				return 'WP_ROCKET_WHITE_LABEL_ACCOUNT' === $name ? $config['white_label'] : $default;
			}
		);

		// White label is checked first: nothing else is read.
		if ( $config['white_label'] ) {
			Functions\expect( 'get_user_meta' )->never();
			$this->options->shouldReceive( 'get' )->never();
			$this->user->shouldReceive( 'is_license_expired' )->never();
			$this->banner->shouldReceive( 'generate' )->never();

			$this->assertSame( '', $this->banner->add_banner( '' ) );

			return;
		}

		Functions\when( 'get_current_user_id' )->justReturn( $config['user_id'] );
		Functions\expect( 'get_user_meta' )
			->once()
			->with( $config['user_id'], 'rocket_boxes', true )
			->andReturn( $config['boxes'] );

		// The gating stops at the first failing condition, in the same order as the former admin notice.
		$this->options->shouldReceive( 'get' )->with( 'async_css', 0 )->andReturn( $config['async_css'] );
		$this->user->shouldReceive( 'is_license_expired' )->andReturn( $config['expired_license'] );

		Filters\expectApplied( 'rocket_disable_rucss_setting' )
			->atMost()
			->once()
			->with( [ 'disable' => false, 'text' => '' ] )
			->andReturn( $config['rucss_status'] );

		if ( $expected ) {
			Functions\when( 'admin_url' )->alias( function ( $path ) {
				return 'https://example.org/wp-admin/' . $path;
			} );
			Functions\when( 'wp_nonce_url' )->alias( function ( $url, $action ) {
				return $url . '&_wpnonce=' . $action;
			} );
			Functions\when( 'add_query_arg' )->alias( function ( $args, $url ) {
				return $url . '&action=' . $args['action'];
			} );

			$this->banner->shouldReceive( 'generate' )
				->once()
				->with(
					'switch-to-rucss-banner',
					[
						'message'     => 'We highly recommend the <b>updated Remove Unused CSS</b> for a better CSS optimization. Load CSS Asynchronously is always available as a back-up.',
						'switch_url'  => 'https://example.org/wp-admin/admin-post.php&_wpnonce=rucss_switch&action=switch_to_rucss',
						'dismiss_url' => 'https://example.org/wp-admin/admin-post.php?action=rocket_ignore&box=switch_to_rucss_notice&_wpnonce=rocket_ignore_switch_to_rucss_notice',
					]
				)
				->andReturn( '<div>rucss</div>' );
		} else {
			$this->banner->shouldReceive( 'generate' )->never();
		}

		$this->assertSame( $expected ? '<div>rucss</div>' : '', $this->banner->add_banner( '' ) );
	}

	public function testShouldReturnExistingHtmlUnchangedWithoutAnyCheck() {
		Functions\expect( 'get_user_meta' )->never();
		$this->options->shouldReceive( 'get' )->never();
		$this->user->shouldReceive( 'is_license_expired' )->never();
		$this->banner->shouldReceive( 'generate' )->never();

		$this->assertSame( '<div>license</div>', $this->banner->add_banner( '<div>license</div>' ) );
	}

	public function testShouldReturnEmptyStringWhenGenerateReturnsNull() {
		Functions\when( 'get_current_user_id' )->justReturn( 42 );
		Functions\when( 'get_user_meta' )->justReturn( '' );
		Functions\when( 'admin_url' )->returnArg();
		Functions\when( 'wp_nonce_url' )->returnArg();
		Functions\when( 'add_query_arg' )->returnArg( 2 );
		$this->options->shouldReceive( 'get' )->andReturn( true );
		$this->user->shouldReceive( 'is_license_expired' )->andReturn( false );
		$this->banner->shouldReceive( 'generate' )->once()->andReturn( null );

		$this->assertSame( '', $this->banner->add_banner( '' ) );
	}
}
