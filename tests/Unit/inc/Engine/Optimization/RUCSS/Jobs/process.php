<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Jobs;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager;
use WP_Rocket\Tests\Unit\HasLoggerTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Jobs\Manager::process
 *
 * @group RUCSS
 */
class Test_Process extends TestCase {
	use HasLoggerTrait;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * UsedCSS query mock.
	 *
	 * @var UsedCSS
	 */
	protected $query;

	/**
	 * Filesystem mock.
	 *
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * Context mock.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Set up the test environment.
	 *
	 * @return void
	 */
	protected function set_up() {
		parent::set_up();

		$this->query      = $this->createPartialMock( UsedCSS::class, [ 'make_status_failed', 'make_status_completed' ] );
		$this->filesystem = Mockery::mock( Filesystem::class );
		$this->context    = Mockery::mock( ContextInterface::class );
		$options          = Mockery::mock( Options_Data::class );

		$this->manager = new Manager( $this->query, $this->filesystem, $this->context, $options );
		$this->set_logger( $this->manager );
	}

	/**
	 * Tests process() handles write success and write failure correctly.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected outcomes.
	 * @return void
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$this->context->shouldReceive( 'is_allowed' )->andReturn( true );

		$job_details = $config['job_details'];
		$row_details = new UsedCSS_Row( $config['row_details'] );

		Filters\expectApplied( 'rocket_rucss_hash' )
			->once()
			->andReturnUsing(
				function ( $hash ) {
					return $hash;
				}
			);

		$this->filesystem->shouldReceive( 'write_used_css' )
			->once()
			->andReturn( $config['write_result'] );

		if ( $expected['make_status_completed'] ) {
			$this->query->expects( self::once() )
				->method( 'make_status_completed' )
				->with( $row_details->url, $row_details->is_mobile, self::isType( 'string' ) );

			$this->query->expects( self::never() )
				->method( 'make_status_failed' );

			Functions\expect( 'set_transient' )->never();
		} else {
			$this->query->expects( self::never() )
				->method( 'make_status_completed' );

			$expected_error_message = $expected['error_message'];
			$this->query->expects( self::once() )
				->method( 'make_status_failed' )
				->with(
					$row_details->url,
					$row_details->is_mobile,
					'',
					$expected_error_message
				);

			$this->logger->shouldReceive( 'error' )
				->once()
				->with( $expected_error_message );

			Functions\expect( 'get_current_blog_id' )->once()->andReturn( 1 );

			Functions\expect( 'set_transient' )
				->once()
				->withArgs(
					function ( $transient_key, $url, $expiry ) use ( $row_details ) {
						return 'rocket_rucss_subfolder_permission_error_1' === $transient_key
							&& $url === $row_details->url
							&& DAY_IN_SECONDS === $expiry;
					}
				);
		}

		$this->manager->process( $job_details, $row_details, $config['optimization_type'] );
	}
}
