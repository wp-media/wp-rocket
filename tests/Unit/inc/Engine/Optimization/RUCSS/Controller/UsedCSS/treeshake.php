<?php

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Queue\QueueInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Row\UsedCSS as UsedCSS_Row;
use WP_Rocket\Engine\Optimization\RUCSS\Frontend\APIClient;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::treeshake
 *
 * @group  RUCSS
 */
class Test_Treeshake extends TestCase {
	protected $options;
	protected $usedCssQuery;
	protected $resourcesQuery;
	protected $api;
	protected $queue;
	protected $usedCss;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		// fix a bug from mockery with __isset function
		error_reporting('E_ALL ^ E_STRICT');
		$this->usedCssQuery = Mockery::mock(UsedCSS_Query::class);
		$this->resourcesQuery = Mockery::mock(ResourcesQuery::class);
		$this->api = Mockery::mock(APIClient::class);
		$this->queue = Mockery::mock(QueueInterface::class);
		$this->usedCss = Mockery::mock(UsedCSS::class . '[is_allowed,update_last_accessed]', [$this->options, $this->usedCssQuery,
				$this->resourcesQuery,
			$this->api,
			$this->queue]);
	}

	protected function tearDown(): void
	{
		error_reporting('E_ALL');
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Logger::disable_debug();
		$wp = new WP();
		$GLOBALS['wp'] = $wp;

		Functions\expect( 'home_url' )
			->with()
			->zeroOrMoreTimes()
			->andReturn( $config['home_url'] );

		$this->usedCss->expects()->is_allowed()->andReturn($config['is_allowed']);

		$this->configureIsMobile($config);

		$this->configureGetExistingUsedCss($config);

		$this->configureCreateNewJob($config);

		$this->configValidUsedCss($config);

		$this->configApplyUsedCss($config);

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
			$usedCssRow = Mockery::mock(UsedCSS_Row::class);
			$usedCssRow->status = $config['get_existing_used_css']['used_css']->status;
			$usedCssRow->css = $config['get_existing_used_css']['used_css']->css;
			$usedCssRow->id = $config['get_existing_used_css']['used_css']->id;
		} else {
			$usedCssRow = null;
		}

		$this->usedCssQuery->expects()->get_row($config['home_url'], $config['is_mobile']['is_mobile'])->andReturn($usedCssRow);

	}

	protected function configureCreateNewJob($config) {
		if(!$config['create_new_job']) {
			return;
		}

		$this->options->expects()->get('remove_unused_css_safelist', [])->andReturn($config['create_new_job']['safelist']);

		Brain\Monkey\Filters\expectApplied('rocket_rucss_safelist')->with($config['create_new_job']['safelist'])->andReturn($config['create_new_job']['safelist']);

		$this->api->expects()->add_to_queue($config['home_url'], $config['create_new_job']['config'])->andReturn
		($config['create_new_job']['response']);
		if(! $config['create_new_job']['is_success_response']){
			return;
		}

		$this->usedCssQuery->expects()->create_new_job($config['home_url'], $config['create_new_job']['response']['contents']['jobId'],
			$config['create_new_job']['response']['contents']['queueName'], $config['is_mobile']['is_mobile'] );
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

		$this->usedCssQuery->expects()->update_last_accessed($config['get_existing_used_css']['used_css']->id);
	}
}
