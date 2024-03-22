<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\Cron\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\JobManager\Cron\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Common\JobManager\JobProcessor;
use WP_Rocket\Engine\Common\Database\Tables\AbstractTable;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Factory as RUCSSFactory;
use WP_Rocket\Engine\Media\AboveTheFold\Jobs\Factory as ATFFactory;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Common\JobManager\Cron\Subscriber::cron_clean_rows
 *
 */
class Test_CronCleanRows extends TestCase {
	private $factories;
	private $subscriber;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		Functions\when('current_time')->justReturn('current_date');
	}

	public function setUp() : void {
		parent::setUp();

		$this->factories = [
			Mockery::mock( RUCSSFactory::class ),
			Mockery::mock( ATFFactory::class ),
		];
		
		$this->subscriber = new Subscriber( Mockery::mock( JobProcessor::class ), $this->factories );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config ) {
		Functions\expect( 'apply_filters' )
			->with( 'rocket_rucss_deletion_enabled', true )
			->andReturn( $config['deletion_activated'] );

		if ( $config['deletion_activated'] ) {
			foreach ( $this->factories as $factory ) {
				$manager = Mockery::mock( Manager::class );
				$manager->shouldReceive( 'is_allowed' )->once()->andReturn( $config['is_allowed'] );

				$factory->expects()
					->manager()
					->andReturn( $manager );
					
				if ( $config['is_allowed'] ) {
					$table = $this->getMockBuilder( AbstractTable::class )
						->disableOriginalConstructor()
						->getMock();

					$factory->expects()
						->table()
						->andReturn( $table );

					$table->expects( $this->once() )
						->method( 'delete_old_rows' );
				}
			}
		} else {
			foreach ( $this->factories as $factory ) {
				$factory->expects()->manager()->never();
				$factory->expects()->table()->never();	
			}
		}

		$this->subscriber->cron_clean_rows();
	}
}
