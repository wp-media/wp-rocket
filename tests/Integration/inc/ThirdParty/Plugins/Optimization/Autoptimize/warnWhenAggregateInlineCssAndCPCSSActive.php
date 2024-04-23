<?php

declare( strict_types=1 );

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize::warn_when_aggregate_inline_css_and_cpcss_active
 *
 * @group  Autoptimize
 * @group  AdminOnly
 * @group  ThirdParty
 */
class Test_WarnWhenAggregateInlineCssAndCPCSSActive extends TestCase {
	use CapTrait;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();
	}

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept(
			'admin_notices',
			'warn_when_aggregate_inline_css_and_cpcss_active'
		);

		Functions\expect( 'wp_create_nonce' )
			->with( 'warn_when_aggregate_inline_css_and_cpcss_active' )
			->andReturn( '123456' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_notices' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddExpectedNotice( $config, $expected ) {
		$this->constants['AUTOPTIMIZE_PLUGIN_VERSION'] = '1.2.3';
		$this->stubRocketGetConstant();

		$current_user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $current_user );

		update_option( 'autoptimize_css', $config['autoptimizeAggregateInlineCSSActive'] );
		update_option( 'autoptimize_css_aggregate', $config['autoptimizeAggregateInlineCSSActive'] );
		update_option( 'autoptimize_css_include_inline', $config['autoptimizeAggregateInlineCSSActive'] );
		add_filter( 'pre_get_rocket_option_async_css', function () use ( $config ) {
			return $config['cpcssActive'];
		} );

		if ( isset( $config['notWPRDashboard'] ) && $config['notWPRDashboard'] ) {
			set_current_screen( 'post' );
		} else {
			set_current_screen( 'settings_page_wprocket' );
		}

		if ( $config['dismissed'] ) {
			update_user_meta(
				$current_user,
				'rocket_boxes',
				[ 'warn_when_aggregate_inline_css_and_cpcss_active' ]
			);
		}

		ob_start();
		do_action( 'admin_notices' );
		$notices = ob_get_clean();
		$notices = empty( $notices ) ? $notices : $this->format_the_html( $notices );

		$this->assertSame( $this->format_the_html( $expected ), $notices );

		$boxes = get_user_meta( $current_user, 'rocket_boxes', true );

		if ( $config['dismissed']
		     &&
		     $config['cpcssActive']
		     &&
		     'on' === $config['autoptimizeAggregateInlineCSSActive']
		) {
			$this->assertContains( 'warn_when_aggregate_inline_css_and_cpcss_active', $boxes );
		}

		if (
			( $config['dismissed']  )
			&&
			( ! $config['cpcssActive'] || 'off' === $config['autoptimizeAggregateInlineCSSActive'] )
		) {
			$this->assertNotContains( 'warn_when_aggregate_inline_css_and_cpcss_active', $boxes );
		}
	}
}
