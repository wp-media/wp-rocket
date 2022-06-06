<?php

$sitemap = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>http://www.photoshoptuto.com/</loc>
        <lastmod>2013-02-08T08:29:01+00:00</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>http://www.photoshoptuto.com/tuto/epouvantail-effrayant-1018</loc>
        <lastmod>2013-02-08T08:29:01+00:00</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.2</priority>
    </url>
    <url>
        <loc>http://www.photoshoptuto.com/proposer-un-tuto</loc>
        <lastmod>2009-12-21T18:19:47+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
    <url>
        <loc>http://www.photoshoptuto.com/tag/35mm</loc>
        <changefreq>weekly</changefreq>
        <priority>0.3</priority>
    </url>
    <sitemap>
        <loc>http://www.photoshoptuto.com/sitemap/1</loc>
        <changefreq>weekly</changefreq>
        <priority>0.3</priority>
    </sitemap>
    <sitemap>
        <loc>http://www.photoshoptuto.com/sitemap/2</loc>
        <changefreq>weekly</changefreq>
        <priority>0.3</priority>
    </sitemap>
</urlset>
XML;

return [
	'errorOnFetchShouldAddNoJob' => [
		'config' => [
			'sitemap_url' => 'http://example.com',
			'process_generate' => [
				'is_wp_error' => true,
				'response' => $sitemap
			]
		],
		'expected' => [
			'children_exists' => false,
			'links_exists' => false,
			'children' => [
				'http://www.photoshoptuto.com/sitemap/2',
				'http://www.photoshoptuto.com/sitemap/2',
			],
			'links' => [
				'http://www.photoshoptuto.com/',
				'http://www.photoshoptuto.com/tuto/epouvantail-effrayant-1018',
				'http://www.photoshoptuto.com/proposer-un-tuto',
				'http://www.photoshoptuto.com/tag/35mm',
			]
		],
	],
	'fetchSitemapShouldCreateJobs' => [
		'config' => [
			'sitemap_url' => 'http://example.com',
			'process_generate' => [
				'response' => $sitemap
			]
		],
		'expected' => [
			'children_exists' => true,
			'links_exists' => true,
			'children' => [
				'http://www.photoshoptuto.com/sitemap/2',
				'http://www.photoshoptuto.com/sitemap/2',
			],
			'links' => [
				'http://www.photoshoptuto.com',
				'http://www.photoshoptuto.com/tuto/epouvantail-effrayant-1018',
				'http://www.photoshoptuto.com/proposer-un-tuto',
				'http://www.photoshoptuto.com/tag/35mm',
			]
		]
	]
];
