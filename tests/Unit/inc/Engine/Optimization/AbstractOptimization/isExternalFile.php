<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\AbstractOptimization;

use ReflectionClass;
use WP_Rocket\Engine\Optimization\AbstractOptimization;
use function Brain\Monkey\Functions;

class Test_IsExternalFile extends \WP_Rocket\Tests\Unit\TestCase {

	protected $optimization;

	public function setUp(): void
	{
		parent::setUp();
		$this->optimization = $this->getMockForAbstractClass(AbstractOptimization::class);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('get_rocket_parse_url')->with($config['url'])-->andReturn($config['file']);
		$this->assertEquals($expected, self::callProtectedMethod($this->optimization, 'is_external_file', array($config['url'])));
	}

	public static function callProtectedMethod($object, $method, array $args=array()) {
		$class = new ReflectionClass(get_class($object));
		$method = $class->getMethod($method);
		$method->setAccessible(true);
		return $method->invokeArgs($object, $args);
	}
}
