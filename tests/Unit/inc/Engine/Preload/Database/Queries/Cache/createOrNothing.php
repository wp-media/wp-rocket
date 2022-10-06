<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use Mockery;
use ReflectionClass;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::create_or_nothing
 *
 * @group Database
 * @group Preload
 */
class Test_CreateOrNothing extends TestCase {
	protected $query;
	protected $logger;

	protected function setUp(): void
	{
		parent::setUp();
		$this->logger = Mockery::mock(Logger::class);
		$this->query = $this->createPartialMock(Cache::class, ['query','add_item', 'format_url']);
		$this->setProtectedProperty($this->query, 'logger', $this->logger);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		Filters\expectApplied('rocket_preload_query_string')->andReturn($config['filter_query']);

		if($config['filter_query']) {
			$this->query->expects(self::once())->method('format_url')->with($config['resource']['url'])->willReturn($config['formatted_url']);
		}

		Functions\when('current_time')->justReturn($config['time']);

		if(! $config['rejected']) {
			$this->query->expects(self::once())->method('query')->with([
				'url' => $config['query']['url'],
			])->willReturn($config['rows']);

			$this->configureCreate($config);
		}

		$this->assertSame($expected, $this->query->create_or_nothing($config['resource']));
	}

	protected function configureCreate($config) {
		if(count($config['rows']) > 0) {
			return;
		}

		if(! $config['id']) {
			$this->logger->expects()->error("Cannot insert {$config['query']['url']} into wpr_rocket_cache");
		}

		$this->query->expects(self::once())->method('add_item')->with($config['save'])->willReturn($config['id']);
	}

	/**
	 * Sets a protected property on a given object via reflection
	 *
	 * @param $object - instance in which protected value is being modified
	 * @param $property - property on instance being modified
	 * @param $value - new value of the property being modified
	 *
	 * @return void
	 */
	public function setProtectedProperty($object, $property, $value)
	{
		$reflection = new ReflectionClass($object);
		$reflection_property = $reflection->getProperty($property);
		$reflection_property->setAccessible(true);
		$reflection_property->setValue($object, $value);
	}
}
