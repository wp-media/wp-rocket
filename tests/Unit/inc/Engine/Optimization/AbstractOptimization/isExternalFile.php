<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\AbstractOptimization;

use WP_Rocket\Engine\Optimization\AbstractOptimization;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

class Test_IsExternalFile extends TestCase {

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
		Functions\expect('get_rocket_parse_url')->with($config['url'])->andReturn($config['file']);
		$this->configureParseContent($config);
		$this->configureCollectHosts($config);
		$this->assertSame($expected, $this->callProtectedMethod($this->optimization, 'is_external_file', array($config['url'])));
	}

	protected function configureParseContent($config) {
		if(! array_key_exists('parse_content', $config)) {
			return;
		}
		Functions\expect('content_url')->with()->andReturn($config['content_url']);
		Functions\expect('wp_parse_url')->with($config['url'])->andReturn($config['url_parsed']);
	}

	protected function configureCollectHosts($config) {
		if(! array_key_exists('collect_hosts', $config)) {
			return;
		}
		$this->optimization->expects(self::once())->method('get_zones')->willReturn($config['zones']);
		Functions\expect('apply_filters')->with('rocket_cdn_hosts', [], $config['zones'])->andReturn($config['cdn_hosts']);
		Functions\expect('get_rocket_i18n_uri')->with()->andReturn($config['lang_hosts']);
		Functions\expect('wp_parse_url')->with($config['lang_url'], PHP_URL_HOST)->andReturn($config['url_host']);
	}

	public function callProtectedMethod($object, $method, array $args=array()) {
		$method = self::get_reflective_method($method, get_class($object));
		return $method->invokeArgs($object, $args);
	}
}
