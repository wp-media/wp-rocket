<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Common\JobManager\Strategy\Strategies\JobSetFail;

use Brain\Monkey\Actions;
use Mockery;
use WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\JobSetFail;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\JobManager\Strategy\Strategies\JobSetFail::execute
 */
class Test_Execute extends TestCase {
	protected $manager;

	public function setUp(): void {
		parent::setUp();
		$this->manager = Mockery::mock( Manager::class );
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldBehaveAsExpected( $config, $expected ) {
		if ( $config['row_details'] ) {
			$row_details = new UsedCSS_Row( $config['row_details'] );
		} else {
			$row_details = null;
		}

		$job_details = [
			'code'    => '',
			'message' => '',
		];

		if ( isset( $config['job_details'] ) ) {
			$job_details = $config['job_details'];
		}

		Actions\expectDone( 'rocket_preload_unlock_url' )->once();

		$this->manager->shouldReceive( 'make_status_failed' )
			->withArgs(
				[
					$row_details->url,
					$row_details->is_mobile,
					$job_details['code'],
					$job_details['message'],
				]
			);

		$strategy = new JobSetFail( $this->manager );
		$strategy->execute( $row_details, $job_details );
	}
}
