<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WPDieException;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\{Database,Settings,Subscriber};
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css_handler
 *
 * @group RUCSS
 */
class Test_TruncateUsedCSSHandler extends TestCase {
	private $settings;
	private $database;
	private $used_css;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/truncateUsedCSSHandler.php';

	protected function setUp(): void {
		parent::setUp();

		$this->settings   = Mockery::mock( Settings::class );
		$this->database   = Mockery::mock( Database::class );
		$this->used_css   = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->settings, $this->database, $this->used_css, Mockery::mock( Queue::class ) );
	}

	protected function tearDown(): void {
		parent::tearDown();

		unset( $_GET['_wpnonce'] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		Functions\stubTranslationFunctions();

		$_GET['_wpnonce'] = $input['nonce'];

		if ( ! isset( $input['nonce'] ) ) {
			Functions\expect( 'wp_verify_nonce' )->never();
			Functions\expect( 'sanitize_key' )->never();
		} else {
			Functions\expect( 'wp_verify_nonce' )->once()->with( $input['nonce'], 'rocket_clear_usedcss' )->andReturn( 'rocket_clear_usedcss' === $input['nonce'] );
			Functions\expect( 'sanitize_key' )->once()->andReturnFirstArg();
		}

		if ( 'rocket_clear_usedcss' === $input['nonce'] ) {
			Functions\expect( 'wp_nonce_ays' )->never();
		} else {
			Functions\expect( 'wp_nonce_ays' )->once()->andReturnUsing( function() {
				throw new WPDieException;
			} );
		}

		if ( ! isset( $input['cap'] ) ) {
			Functions\expect( 'current_user_can' )->never();
		}else {
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_remove_unused_css' )
				->andReturn( $input['cap'] );

			if ( ! $input['cap'] ) {
				Functions\expect( 'wp_die' )
					->once()
					->andReturnUsing(
						function() {
							throw new WPDieException;
						}
					);
			}
		}

		if ( isset( $input['option_enabled'] ) ) {
			$this->settings->shouldReceive( 'is_enabled' )->once()->andReturn( $input['option_enabled'] );

			if ( ! $input['option_enabled'] || $expected['truncated'] ) {
				Functions\expect( 'set_transient' )
					->once()
					->with(
						'rocket_clear_usedcss_response',
						$expected['notice_details']
					);

				Functions\expect( 'wp_get_referer' )->once()->andReturn( 'http://example.org' );
				Functions\expect( 'esc_url_raw' )->once()->with( 'http://example.org' )->andReturnFirstArg();
				Functions\expect( 'wp_safe_redirect' )->once();
				Functions\expect( 'wp_die' )
					->once()
					->andReturnUsing(
						function() {
							throw new WPDieException;
						}
					);
			}

		}

		if ( $expected['truncated'] ) {
			$this->used_css->shouldReceive( 'delete_used_css_rows' );

			Functions\expect( 'set_transient' )
				->once()
				->with(
					'rocket_rucss_processing',
					Mockery::type( 'int' ),
					90
				);

			Functions\when('home_url')->justReturn($input['home_url']);
			Functions\expect( 'rocket_renew_box' )
				->once()
				->with( 'rucss_success_notice' );
			Functions\expect( 'rocket_clean_domain' )->once();
			Functions\expect( 'rocket_dismiss_box' )->with('rocket_warning_plugin_modification')->once();
		}

		$this->expectException( WPDieException::class );

		$this->subscriber->truncate_used_css_handler();
	}
}
