<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPDieException;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::init_critical_css_generation
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_InitCriticalCssGeneration extends TestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WPDieException.php';
	}

	public function setUp() {
		parent::setUp();

		unset( $_GET['_wpnonce'] );
	}

	public function tearDown() {
		unset( $_GET['_wpnonce'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$_GET['_wpnonce'] = $config['nonce'];

		if ( ! isset( $config['nonce'] ) ) {
			Functions\expect( 'wp_verify_nonce' )->never();
		} elseif ( isset( $config['nonce'] ) && 'rocket_generate_critical_css' !== $config['nonce'] ) {
			Functions\expect( 'wp_verify_nonce' )->once()->with( $config['nonce'], 'rocket_generate_critical_css' )->andReturn( false );
		} else {
			Functions\expect( 'wp_verify_nonce' )->once()->with( $config['nonce'], 'rocket_generate_critical_css' )->andReturn( true );
		}

		if ( 'rocket_generate_critical_css' === $config['nonce'] ) {
			Functions\expect( 'wp_nonce_ays' )->never();
		} else {
			Functions\expect( 'wp_nonce_ays' )->once()->andReturnUsing( function() {
				throw new WPDieException;
			} );
		}

		if ( ! isset( $config['cap'] ) ) {
			Functions\expect( 'current_user_can' )->never();
		} elseif ( ! $config['cap'] ) {
			Functions\expect( 'current_user_can' )->once()->with( 'rocket_regenerate_critical_css' )->andReturn( false );
			Functions\expect( 'wp_die' )->once()->andReturnUsing( function() {
				throw new WPDieException;
			} );
		} else {
			Functions\expect( 'current_user_can' )->once()->with( 'rocket_regenerate_critical_css' )->andReturn( true );
		}

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_get_referer' )->justReturn( $config['referer'] );

		$critical_css = Mockery::mock( CriticalCSS::class );
		$subscriber   = new CriticalCSSSubscriber(
			$critical_css,
			Mockery::mock( Options_Data::class )
		);

		if ( ! $expected ) {
			$this->expectException( WPDieException::class );
		} else {
			$critical_css->shouldReceive( 'is_async_css_mobile' )
				->once()
				->andReturn( $config['mobile'] );

			if ( ! $config['mobile'] ) {
				$critical_css->shouldReceive( 'process_handler' )
				->once()
				->with( 'default' );
			} else {
				$critical_css->shouldReceive( 'process_handler' )
				->once()
				->with( 'all' );
			}

			if ( false  === strpos( $config['referer'], 'wprocket' ) ) {
				Functions\expect( 'set_transient' )
					->once()
					->with( 'rocket_critical_css_generation_triggered', 1 );
			} else {
				Functions\expect( 'set_transient' )->never();
			}

			Functions\expect( 'wp_safe_redirect' )->once();
			Functions\when( 'esc_url_raw' )->returnArg();
			Functions\expect( 'wp_die' )->once();
		}
		
		$subscriber->init_critical_css_generation();
	}
}
