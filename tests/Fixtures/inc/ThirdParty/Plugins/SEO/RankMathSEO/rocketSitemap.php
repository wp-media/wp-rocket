<?php
return [
	'optionDisabledShouldReturnSame' => [
		'config' => [
			'is_disabled' => false,
			'sitemap' => 'sitemap',
			'sitemaps' => []
		],
		'expected' => [

		]
	],
	'optionEnableShouldAddSitemap' => [
		'config' => [
			'is_disabled' => true,
			'sitemap' => 'sitemap',
			'sitemaps' => []
		],
		'expected' => [
			'sitemap',
		]
	]
];
