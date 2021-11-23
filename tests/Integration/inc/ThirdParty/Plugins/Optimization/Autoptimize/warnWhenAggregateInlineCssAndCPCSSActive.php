<?php

declare( strict_types=1 );

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize::warn_when_aggregate_inline_css_and_cpcss_active
 *
 * @group  Autoptimize
 * @group  AdminOnly
 * @group  ThirdParty
 * @group  cgtest
 */
class Test_WarnWhenAggregateInlineCssAndCPCSSActive extends TestCase {
	use CapTrait;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();
	}

	public function setUp(): void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept(
			'admin_notices',
			'warn_when_aggregate_inline_css_and_cpcss_active'
		);

		Functions\expect( 'wp_create_nonce' )
			->with( 'warn_when_aggregate_inline_css_and_cpcss_active' )
			->andReturn( '123456' );
	}

	public function tearDown() {
		$this->restoreWpFilter( 'admin_notices' );
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddExpectedNotice( $config, $expected ) {
		$this->constants['AUTOPTIMIZE_PLUGIN_VERSION'] = '1.2.3';
		$this->stubRocketGetConstant();

		$current_user = static::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $current_user );

		update_option( 'aggregate_inline_css', $config['autoptimizeAggregateInlineCSSActive'] );
		add_filter( 'pre_get_rocket_option_critical_css', function () use ( $config ) {
			return $config['cpcssActive'];
		} );

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
