<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Media\Lazyload\CSS\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\Media\Lazyload\CSS\Subscriber::maybe_replace_css_images
 *
 * @group LazyloadCSS
 */
class Test_MaybeReplaceCssImages extends FilesystemTestCase {
	use HttpRequestTrait;

	protected $path_to_test_data = '/inc/Engine/Media/Lazyload/CSS/Subscriber/integration/maybeReplaceCssImages.php';

	protected $config;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		$this->unregisterAllCallbacksExcept('rocket_buffer', 'maybe_replace_css_images', 110000);

		add_filter('pre_get_rocket_option_lazyload_css_bg_img', [$this, 'lazyload_css_bg_img']);
		add_filter('rocket_lazyload_excluded_src', [$this, 'exclude_lazyload']);
		add_filter('rocket_lazyload_css_hash', [$this, 'rocket_lazyload_css_hash'], 10, 2);
		add_filter( 'rocket_disable_meta_generator', '__return_true' );
	}

	public function tear_down() {
		remove_filter('rocket_lazyload_excluded_src', [$this, 'exclude_lazyload']);
		remove_filter('pre_get_rocket_option_lazyload_css_bg_img', [$this, 'lazyload_css_bg_img']);
		remove_filter('rocket_lazyload_css_hash', [$this, 'rocket_lazyload_css_hash']);
		remove_filter( 'rocket_disable_meta_generator', '__return_true' );

		$this->restoreWpHook('rocket_buffer');

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		$this->config         = $config;
		$this->config['http'] = [
			$config['no_background']['url'] => $config['no_background']['response'],
			$config['external']['url']      => $config['external']['response'],
		];

		Functions\when('current_time')->justReturn($config['current_time']);

		Functions\when('rocket_get_constant')->alias(function ($name, $default = null) {
			if('ABSPATH' === $name) {
				return $this->filesystem->getUrl('/');
			}

			if('WP_CONTENT_DIR' === $name) {
				return $this->filesystem->getUrl('/') . 'wp-content';
			}

			if(defined($name)) {
				return constant($name);
			}

			return $default;
		});

		Functions\when('wp_generate_uuid4')->alias(function () {
			return 'hash';
		});

		$this->assertSame($this->format_the_html($expected['output']), $this->format_the_html(apply_filters('rocket_buffer', $config['html'])));
		foreach($expected['files'] as $path => $content) {

			$this->assertSame($content['exists'], $this->filesystem->exists($path), "$path is incoherent");
			if(! $content['exists']) {
				continue;
			}

			$expected_content = trim($content['content']);
			$content = trim($this->filesystem->get_contents($path));

			$this->assertSame($expected_content, $content, "$path");

		}
	}

	public function lazyload_css_bg_img() {
		return $this->config['lazyload_css_bg_img'];
	}

	public function rocket_lazyload_css_hash( $hash, $url_tag ) {
		if ( $this->config && array_key_exists( $url_tag['url'], $this->config['hash_mapping'] ) ) {
			return $this->config['hash_mapping'][ $url_tag['url'] ];
		}
		return $hash;
	}

	public function exclude_lazyload() {
		return $this->config['excluded'];
	}
}
