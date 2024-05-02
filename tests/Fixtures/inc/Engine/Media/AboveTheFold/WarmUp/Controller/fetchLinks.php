<?php
$html_no_found_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button></body></html>';
$html_no_valid_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="#hero">Goto Top</a><a href="javascript:void(0)">Click Bait</a></body></html>';
$html_valid_links_among_invalid_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="#hero">Goto Top</a><a href="javascript:void(0)">Click Bait</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_external_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://wordpress.org/hello-world">Hello World</a><a href="https://wordpress.org/another-day">Another Day</a><a href="https://wordpress.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_valid_links_among_external_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://wordpress.org/hello-world">Hello World</a><a href="https://wordpress.org/another-day">Another Day</a><a href="https://wordpress.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_links_without_duplicate = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/rebecca-brown-he-came-to-set-the-captives-free">Buy (He came to set the captives free) - Rebecca Brown</a></body></html>';
$html_links_with_relative_url = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="/rebecca-brown-he-came-to-set-the-captives-free">Buy (He came to set the captives free) - Rebecca Brown</a></body></html>';
$html_with_ten_links_and_home = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin="preconnect"/><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all"/></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://example.org/hello-world-2">Hello World 2</a><a href="https://example.org/hello-world-3">Hello World 3</a><a href="https://example.org/hello-world-4">Hello World 4</a><a href="https://example.org/hello-world-5">Hello World 5</a><a href="https://example.org/hello-world-6">Hello World 6</a><a href="https://example.org/hello-world-7">Hello World 7</a><a href="https://example.org/hello-world-8">Hello World 8</a><a href="https://example.org/hello-world-9">Hello World 9</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/rebecca-brown-he-came-to-set-the-captives-free">Buy (He came to set the captives free) - Rebecca Brown</a><a href="https://example.org">Home</a></body></html>';
$html_with_external_links_before_internal = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin="preconnect"><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all"></head><body><ul><li><a href="/delay-js-script-add-to-any">Link internal relative 1/1</a></li><li><a href="https://beautifier.io/">Link external 1//2</a></li><li><a href="https://translate.google.com/">Link external 2/3</a></li><li><a href="https://www.timeanddate.com/">Link external 3/4</a></li><li><a href="https://loremipsum.io/">Link external 4/5</a></li><li><a href="https://wordpress.org/plugins/wp-dummy-content-generator/">Link external 5/6</a></li><li><a href="https://validator.w3.org/#validate_by_uri">Link external 6/7</a></li><li><a href="">Link missing 8</a></li><li><a href="/accordion-toggle">Link internal relative 2</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 1/9</a></li><li><a href="https://example.org/countdown-timer">Link internal absolute 2/10</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 3/11</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 4/12</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 5/13</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 6/14</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 7/15</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 8/16</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 9/17</a></li><li><a href="https://example.org/cover-galleries">Link internal absolute 10/18</a></li><li><a href="https://example.org/cover-galleries-2">Link internal absolute 11/19</a></li><li><a href="https://example.org/cover-galleries-3">Link internal absolute 12/20</a></li><li><a href="https://example.org/cover-galleries-4">Link internal absolute 13/21</a></li><li><a href="https://example.org/cover-galleries-5">Link internal absolute 14/22</a></li><li><a href="https://example.org/cover-galleries-6">Link internal absolute 15/23</a></li><li><a href="https://example.org/cover-galleries-7">Link internal absolute 16/24</a></li></ul></body></html>';
$html_with_rss_feed_rest_api = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin="preconnect"><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all"></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://example.org/feed">RSS Feed</a><a href="https://example.org/wp-json/wp/v2/users">Rest API</a><a href="https://example.org/hello-world-4">Hello World 4</a><a href="https://example.org/hello-world-5">Hello World 5</a><a href="https://example.org/hello-world-6">Hello World 6</a><a href="https://example.org/hello-world-7">Hello World 7</a><a href="https://example.org/hello-world-8">Hello World 8</a><a href="https://example.org/hello-world-9">Hello World 9</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/rebecca-brown-he-came-to-set-the-captives-free">Buy (He came to set the captives free) - Rebecca Brown</a><a href="https://example.org">Home</a></body></html>';

return [
	'shouldReturnEmptyWhenLicenseExpired' => [
		'config' => [
			'license_expired' => true,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'response' => [
				'response' => [
					'code'    => 500,
				],
			],
		],
		'expected' => [],
	],
	'shouldReturnEmptyWhenNot200' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'response' => [
				'response' => [
					'code'    => 500,
				],
			],
		],
		'expected' => [],
	],
	'shouldReturnEmptyWhenNoFoundLinks' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => false,
			'response' => [
				'body'    => $html_no_found_links,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [],
	],
	'shouldReturnOnlyHomeWithNoValidLinks' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_no_valid_links,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org',
		],
	],
	'shouldReturnValidLinksAmongInvalidLinks' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_valid_links_among_invalid_links,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world',
			'https://example.org/another-day',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org',
		],
	],
	'shouldReturnOnlyHomeWithExternalLinks' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_external_links,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org',
		],
	],
	'shouldReturnValidLinksAmongExternalLinks' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_valid_links_among_external_links,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world',
			'https://example.org/another-day',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org',
		],
	],
	'shouldReturnLinksWithoutDuplicate' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_links_without_duplicate,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world',
			'https://example.org/another-day',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org/rebecca-brown-he-came-to-set-the-captives-free',
			'https://example.org',
		],
	],
	'shouldReturnLinksWithRelativeUrl' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_links_with_relative_url,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world',
			'https://example.org/another-day',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org/rebecca-brown-he-came-to-set-the-captives-free',
			'https://example.org',
		],
	],
	'shouldReturnTenLinksPlusHome' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_with_ten_links_and_home,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world-2',
			'https://example.org/hello-world-3',
			'https://example.org/hello-world-4',
			'https://example.org/hello-world-5',
			'https://example.org/hello-world-6',
			'https://example.org/hello-world-7',
			'https://example.org/hello-world-8',
			'https://example.org/hello-world-9',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org/rebecca-brown-he-came-to-set-the-captives-free',
			'https://example.org',
		],
	],
	'shouldReturnTenLinksWithExternalLinksBeforeInternal' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_with_external_links_before_internal,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/delay-js-script-add-to-any',
			'https://example.org/accordion-toggle',
			'https://example.org/cover-galleries',
			'https://example.org/countdown-timer',
			'https://example.org/cover-galleries-2',
			'https://example.org/cover-galleries-3',
			'https://example.org/cover-galleries-4',
			'https://example.org/cover-galleries-5',
			'https://example.org/cover-galleries-6',
			'https://example.org/cover-galleries-7',
			'https://example.org',
		],
	],
	'shouldReturnLinksWithoutRSSAndRestAPILink' => [
		'config' => [
			'license_expired' => false,
			'device' => 'desktop',
			'headers' => [
				'user-agent' => 'WP Rocket/Pre-fetch Home Links',
				'timeout'    => 60,
			],
			'found_link' => true,
			'response' => [
				'body'    => $html_with_rss_feed_rest_api,
				'response' => [
					'code'    => 200,
				],
			],
		],
		'expected' => [
			'https://example.org/hello-world-4',
			'https://example.org/hello-world-5',
			'https://example.org/hello-world-6',
			'https://example.org/hello-world-7',
			'https://example.org/hello-world-8',
			'https://example.org/hello-world-9',
			'https://example.org/rich-dad-poor-dad',
			'https://example.org/rebecca-brown-he-came-to-set-the-captives-free',
			'https://example.org',
		],
		'shouldReturnLinksWithMobileHeader' => [
			'config' => [
				'license_expired' => false,
				'device' => 'mobile',
				'headers' => [
					'user-agent' => 'WP Rocket/Pre-fetch Home Links Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1',
					'timeout'    => 60,
				],
				'found_link' => true,
				'response' => [
					'body'    => $html_valid_links_among_invalid_links,
					'response' => [
						'code'    => 200,
					],
				],
			],
			'expected' => [
				'https://example.org/hello-world',
				'https://example.org/another-day',
				'https://example.org/rich-dad-poor-dad',
				'https://example.org',
			],
		],
	],
];
