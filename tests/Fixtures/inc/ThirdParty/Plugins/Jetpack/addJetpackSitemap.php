<?php
return [
	'disabledShouldAddNothing' => [
		'config' => [
			'is_enabled' => false,
			'jetpack_sitemap' => 'sitemap',
			'sitemaps' => [

			]
		],
		'expected' => [

		]
	],
	'enabledShouldAddSitemap' => [
		'config' => [
			'is_enabled' => true,
			'jetpack_sitemap' => 'sitemap',
			'sitemaps' => [

			]
		],
		'expected' => [
			'jetpack' => 'sitemap',
		]
	]
];
