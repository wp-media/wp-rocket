<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Common\JobManager\Queue\Queue;
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
					'rocket_saas_processing',
					Mockery::type( 'int' ),
					90
				);

			Functions\expect( 'rocket_renew_box' )
				->once()
				->with( 'saas_success_notice' );
		} else {
			Functions\expect( 'set_transient' )->never();
			Functions\expect( 'rocket_renew_box' )->never();
		}

		$this->subscriber->maybe_set_processing_transient( $input['old_value'], $input['value'] );
	}
}
