<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WPDieException;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::init_critical_css_generation
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::process_handler
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::cancel_process
 * @uses   ::rocket_get_constant
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_InitCriticalCssGeneration extends TestCase {
	use SubscriberTrait;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WPDieException.php';
	}

	protected function setUp(): void {
		parent::setUp();

		$this->stubEscapeFunctions();
		unset( $_GET['_wpnonce'] );

		$this->setUpTests();
	}

	protected function tearDown(): void {
		unset( $_GET['_wpnonce'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->assertNonce( $config );
		$this->assertCap( $config );

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'wp_get_referer' )->justReturn( $config['referer'] );

		if ( ! $expected ) {
			$this->expectException( WPDieException::class );
		} else {
			$this->critical_css
				->shouldReceive( 'is_async_css_mobile' )
				->once()
				->andReturn( $config['mobile'] );

			if ( ! $config['mobile'] ) {
				$this->critical_css
					->shouldReceive( 'process_handler' )
					->once()
					->with( 'default' );
			} else {
				$this->critical_css
					->shouldReceive( 'process_handler' )
					->once()
					->with( 'all' );
			}

			if ( false === strpos( $config['referer'], 'wprocket' ) ) {
				Functions\expect( 'set_transient' )
					->once()
					->with( 'rocket_critical_css_generation_triggered', 1 );
			} else {
				Functions\expect( 'set_transient' )->never();
			}

			Functions\expect( 'wp_safe_redirect' )->once();
			Functions\expect( 'wp_die' )->once();
		}

		$this->subscriber->init_critical_css_generation();
	}

	private function assertNonce( $config ) {
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
	}

	private function assertCap( $config ) {
		if ( ! isset( $config['cap'] ) ) {
			Functions\expect( 'current_user_can' )->never();

			return;

		}

		if ( $config['cap'] ) {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_regenerate_critical_css' )
				->andReturn( true );

			return;
		}

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_regenerate_critical_css' )
			->andReturn( false );

		Functions\expect( 'wp_die' )
			->once()
			->andReturnUsing(
				function() {
					throw new WPDieException;
				}
			);
	}
}
