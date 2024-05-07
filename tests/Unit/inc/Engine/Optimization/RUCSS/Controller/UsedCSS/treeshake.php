<?php

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\Filesystem;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Fixtures\inc\Engine\Common\JobManager\Manager;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::treeshake
 *
 * @group  RUCSS
 */
class Test_Treeshake extends TestCase {
	protected $options;
	protected $usedCssQuery;
	protected $queue;
	protected $data_manager;
	protected $filesystem;
	protected $context;
	protected $usedCss;
	protected $manager;

	/**
	 * @var StrategyFactory
	 */
	protected $strategy_factory;

	/**
	 * @var WPRClock
	 */
	protected $wpr_clock;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->usedCssQuery = $this->createMock(UsedCSS_Query::class);
		$this->data_manager = Mockery::mock( DataManager::class );
		$this->filesystem = Mockery::mock( Filesystem::class );
		$this->context = Mockery::mock(ContextInterface::class);
		$this->manager = Mockery::mock(Manager::class);

		$this->usedCss = Mockery::mock(
			UsedCSS::class . '[is_allowed,update_last_accessed]',
			[
				$this->options,
				$this->usedCssQuery,
				$this->data_manager,
				$this->filesystem,
				$this->context,
				$this->manager
			]
		);
	}

	protected function tearDown(): void
	{
		error_reporting(E_ALL);
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$wp = new WP();
		$GLOBALS['wp'] = $wp;

		Functions\expect( 'home_url' )
			->with()
			->zeroOrMoreTimes()
			->andReturn( $config['home_url'] );

		$this->context->expects()->is_allowed()->andReturn($config['is_allowed']);

		$this->configureIsMobile($config);

		$this->configureGetExistingUsedCss($config);

		$this->configureCreateNewJob($config);

		$this->configValidUsedCss($config);

		$this->configApplyUsedCss($config);

		$dynamic_lists = [];

		if ( isset( $config['dynamic_lists'] ) ) {
			$dynamic_lists = (object) $config['dynamic_lists'];
		}

		$this->data_manager->shouldReceive( 'get_lists' )
			->atMost()
			->once()
			->andReturn( $dynamic_lists );

		$this->assertEquals($this->format_the_html($expected), $this->format_the_html($this->usedCss->treeshake($config['html'])));
	}

	protected function configureIsMobile($config) {
		if(! key_exists('is_mobile', $config)) {
			return;
		}

		$this->options->expects()->get('cache_mobile', 0)->andReturn($config['is_mobile']['has_mobile_cache']);

		if(! $config['is_mobile']['has_mobile_cache']) {
			return;
		}

		$this->options->expects()->get('do_caching_mobile_files', 0)->andReturn($config['is_mobile']['is_caching_mobile_files']);

		if(! $config['is_mobile']['is_caching_mobile_files']) {
			return;
		}

		Functions\expect( 'wp_is_mobile' )
			->with()
			->once()
			->andReturn( $config['is_mobile']['is_mobile'] );
	}

	protected function configureGetExistingUsedCss($config) {

		if(! key_exists('get_existing_used_css', $config)) {
			return;
		}

		Functions\expect( 'add_query_arg' )
			->with( [], $config['home_url'] )
			->once()
			->andReturn( $config['home_url'] );

		if($config['get_existing_used_css']['used_css']) {
			$usedCssRow = new UsedCSS_Row($config['get_existing_used_css']['used_css']);
		} else {
			$usedCssRow = null;
		}

		$this->usedCssQuery->expects(self::atLeastOnce())->method('get_row')->with($config['home_url'], $config['is_mobile']['is_mobile'])->willReturn($usedCssRow);

		if ( ! empty( $config['get_existing_used_css']['used_css']->hash ) ) {
			$this->filesystem->shouldReceive( 'get_used_css' )
				->atMost()
				->once()
				->with( $config['get_existing_used_css']['used_css']->hash )
				->andReturn( $config['get_existing_used_css']['used_css']->css );
		}
	}

	protected function configureCreateNewJob($config) {
		if(!key_exists('create_new_job', $config) || !$config['create_new_job']) {
			return;
		}

		$this->manager->shouldReceive( 'add_url_to_the_queue' )
                    ->withArgs([$config['home_url'], $config['is_mobile']['is_mobile']]);
	}

	protected function configValidUsedCss($config) {
		if(! key_exists('valid_used_css', $config)) {
			return;
		}
	}

	protected function configApplyUsedCss($config) {
		if(! key_exists('apply_used_css', $config)) {
			return;
		}

		$this->usedCssQuery->expects(self::once())->method('update_last_accessed')->with($config['get_existing_used_css']['used_css']->id);
	}
}
