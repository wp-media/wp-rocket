<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WPDieException;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::maybe_set_processing_transient
 *
 * @group  RUCSS
 */
class Test_MaybeSetProcessingTransient extends TestCase {
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->subscriber = new Subscriber( Mockery::mock( Settings::class ), Mockery::mock( Database::class ), Mockery::mock( UsedCSS::class ), Mockery::mock( Queue::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		if ( $expected ) {
			Functions\expect( 'set_transient' )
				->once()
				->with(
					'rocket_rucss_processing',
					Mockery::type( 'int' ),
					60
				);

			Functions\when( 'site_url' )
				->justReturn( 'http://example.org/wp-cron.php' );

			Functions\expect( 'wp_safe_remote_get' )
				->once()
				->with(
					'http://example.org/wp-cron.php',
					[
						'blocking' => false,
						'timeout'  => 0.01,
					]
				);
		} else {
			Functions\expect( 'set_transient' )->never();
		}

		$this->subscriber->maybe_set_processing_transient( $input['old_value'], $input['value'] );
	}
}
