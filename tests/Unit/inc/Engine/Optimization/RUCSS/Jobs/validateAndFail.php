<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Optimization\RUCSS\Jobs;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;
use WP_Rocket\Logger\Logger;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager::validate_and_fail
 */
class Test_validateAndFail extends TestCase {
	use HasLoggerTrait;

	/**
	 * @var Manager
	*/
	protected $manager;

	protected $logger;

	protected $context;

	public function set_up() {
		parent::set_up();

		$query = $this->createPartialMock(UsedCSS::class, ['add_item']);
		$file  = Mockery::mock(Filesystem::class);
		$this->context = Mockery::mock(ContextInterface::class);
		$options = Mockery::mock(Options_Data::class);
		$this->manager = new Manager( $query, $file, $this->context, $options );
		$this->set_logger($this->manager);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->context->shouldReceive('is_allowed')
			->andReturn(1);
		$manager = Mockery::mock( Manager::class );

		$job_details = $config['job_details'];
		$row_details = new UsedCSS_Row($config['row_details']);

		if( $config['optimization_type'] !== 'all' ) {
			Filters\expectApplied( 'rocket_min_rucss_size' )->andReturn( $config['min_size'] );

			$manager->shouldReceive( 'make_status_failed' )
				->withArgs( [ $row_details->url, $row_details->is_mobile, strval( $config['job_details']['code'] ), $config['job_details']['message'] ] );

			$this->logger->shouldReceive('error')
				->once()
				->withArgs(function ($message) {
					return strpos($message, 'RUCSS: shakedCSS size is less than') !== false;
				});

			$this->manager->validate_and_fail( $job_details, $row_details, $config['optimization_type']);

			return;
		}

		$this->logger->shouldNotReceive('error');

		$this->manager->validate_and_fail($job_details, $row_details, $config['optimization_type']);
	}
}
