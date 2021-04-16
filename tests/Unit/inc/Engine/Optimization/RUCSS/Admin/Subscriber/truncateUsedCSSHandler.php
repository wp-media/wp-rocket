<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WPDieException;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::truncate_used_css_handler
 *
 * @group  RUCSS
 */
class Test_TruncateUsedCSSHandler extends TestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/TruncateUsedCSSHandler.php';

	public function setUp() : void {
		parent::setUp();

		$this->settings   = Mockery::mock( Settings::class );
		$this->database   = Mockery::mock( Database::class );
		$this->usedCSS    = Mockery::mock( UsedCSS::class );
		$this->subscriber = new Subscriber( $this->settings, $this->database, $this->usedCSS );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		Functions\stubTranslationFunctions();

		$_GET['_wpnonce'] = $input['nonce'];

		if ( ! isset( $input['nonce'] ) ) {
			Functions\expect( 'wp_verify_nonce' )->never();
		} elseif ( isset( $input['nonce'] ) && 'rocket_clear_usedcss' !== $input['nonce'] ) {
			Functions\expect( 'wp_verify_nonce' )->once()->with( $input['nonce'], 'rocket_clear_usedcss' )->andReturn( false );
		} else {
			Functions\expect( 'wp_verify_nonce' )->once()->with( $input['nonce'], 'rocket_clear_usedcss' )->andReturn( true );
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
		}else{
			Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_manage_options' )
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
						$expected['norice_details']
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

		if ( ! $expected['truncated'] && isset( $expected['reason'] ) ) {
			switch ( $expected['reason'] ) {
				case 'nonce':
				case 'cap':
				case 'option':
					$this->expectException( WPDieException::class );
					break;
			}
		}

		if ( $expected['truncated'] ) {
			$this->database->shouldReceive( 'truncate_used_css_table' )->once();
			Functions\expect( 'rocket_clean_domain' )->once();
			$this->expectException( WPDieException::class );
		}

		$this->subscriber->truncate_used_css_handler();
	}
}
