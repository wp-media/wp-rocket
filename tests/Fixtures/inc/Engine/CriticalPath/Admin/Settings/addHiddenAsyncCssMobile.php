<?php

return [
	'testShouldAddFieldWhenString' => [
		'fields'  => 'test',
		'expected' => [
			'test',
			'async_css_mobile',
		],
	],
	'testShouldAddFieldWhenEmptyArray' => [
		'fields'  => [],
		'expected' => [
			'async_css_mobile',
		],
	],
	'testShouldAddFieldWhenNotEmptyArray' => [
		'fields'  => [
			'consumer_key',
			'consumer_email',
			'secret_key',
			'license',
			'secret_cache_key',
			'minify_css_key',
			'minify_js_key',
			'version',
			'cloudflare_old_settings',
			'sitemap_preload_url_crawl',
			'cache_ssl',
		],
		'expected' => [
			'consumer_key',
			'consumer_email',
			'secret_key',
			'license',
			'secret_cache_key',
			'minify_css_key',
			'minify_js_key',
			'version',
			'cloudflare_old_settings',
			'sitemap_preload_url_crawl',
			'cache_ssl',
			'async_css_mobile',
		],
	],
];
