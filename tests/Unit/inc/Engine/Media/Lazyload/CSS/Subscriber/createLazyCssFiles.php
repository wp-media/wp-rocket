<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Engine\Media\Lazyload\CSS\Subscriber\SubscriberTrait;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::create_lazy_css_files
 */
class Test_createLazyCssFiles extends TestCase {

	use SubscriberTrait;

	public function set_up() {
		$this->init_subscriber();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldReturnAsExpected( $config, $expected )
    {

		Functions\when('wp_generate_uuid4')->justReturn('hash');

		foreach ($config['has'] as $url => $output) {
			$this->filesystem_cache->expects()->has($url)->andReturn($output);
		}

		foreach ($config['resolve'] as $url => $path) {
			$this->file_resolver->expects()->resolve($url)->andReturn($path);
		}

		foreach ($config['content'] as $path => $content) {
			$this->filesystem->expects()->get_contents($path)->andReturn($content);
		}

		foreach ($config['extract'] as $content => $urls) {
			$this->extractor->expects()->extract($content)->andReturn($urls);
		}

		foreach ($config['rule_format'] as $url_tag) {
			$this->rule_formatter->expects()->format($url_tag['content'], $url_tag['tag'])->andReturn($url_tag['new_content']);
			$this->json_formatter->expects()->format($url_tag['tag'])->andReturn($url_tag['formatted_urls']);
		}

		foreach ($config['cache_set'] as $url => $data) {
			$this->filesystem_cache->expects()->set($url, $data['content'])->andReturn($data['output']);
		}

		foreach ($config['cache_get'] as $url => $content) {
			$this->filesystem_cache->expects()->get($url)->andReturn($content);
		}

		foreach ($config['json_set'] as $url => $content) {
			$this->filesystem_cache->expects()->set($url, $content);
		}

		foreach ($config['generate_url'] as $url => $output) {
			$this->filesystem_cache->expects()->generate_url($url)->andReturn($output);
		}

        $this->assertSame($expected, $this->subscriber->create_lazy_css_files($config['data']));
    }
}
