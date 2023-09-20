<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Frontend\SitemapParser;

use WP_Rocket\Engine\Preload\Frontend\SitemapParser;
use WP_Rocket\Tests\Unit\TestCase;
/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\SitemapParser::parse_sitemap
 * @group  Preload
 */
class Test_GetLinks extends TestCase {
	protected $sitemap_parser;

	protected function setUp(): void
	{
		parent::setUp();
		$this->sitemap_parser = new SitemapParser();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->sitemap_parser->set_content($config['content']);
		$this->assertSame($expected, $this->sitemap_parser->get_links());
	}
}
