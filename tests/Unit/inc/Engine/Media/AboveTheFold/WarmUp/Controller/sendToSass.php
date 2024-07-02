<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Media\AboveTheFold\WarmUp\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\APIClient;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Controller;
use WP_Rocket\Engine\Media\AboveTheFold\WarmUp\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Mockery;

class sendToSass extends TestCase {
	private $user;
	private $controller;
	private $context;
	private $queue;
	private $options;

	private $api_client;

	protected function setUp(): void {
		parent::setUp();

		$this->context    = Mockery::mock( ContextInterface::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->api_client = Mockery::mock( APIClient::class );
		$this->user       = Mockery::mock( User::class );
		$this->queue      = Mockery::mock( Queue::class );
		$this->controller = new Controller( $this->context, $this->options, $this->api_client, $this->user, $this->queue );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$mobile_cache = 'mobile' === $config['device'] ? 1 : 0;

		$this->options->shouldReceive('get')
			->with( 'cache_mobile', 1 )
			->andReturn( $mobile_cache );

		$this->options->shouldReceive('get')
			->with( 'do_caching_mobile_files', 1 )
			->andReturn( $mobile_cache );

		$this->api_client->shouldReceive('add_to_atf_queue')
			->with('http://example.com')
			->once()
			->andReturn([$config['url'], []]);

		if('mobile' === $config['device']) {
			$this->api_client->shouldReceive('add_to_atf_queue')
				->with( 'http://example.com', $config['device'] )
				->once()
				->andReturn( [$config['url'], []] );
		}

		$this->controller->send_to_saas($config['url']);
	}
}
