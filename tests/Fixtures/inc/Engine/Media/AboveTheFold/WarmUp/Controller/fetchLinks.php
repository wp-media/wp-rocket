<?php
$html_no_found_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button></body></html>';
$html_no_valid_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="#hero">Goto Top</a><a href="javascript:void(0)">Click Bait</a></body></html>';
$html_valid_links_among_valid_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="#hero">Goto Top</a><a href="javascript:void(0)">Click Bait</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_external_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://wordpress.org/hello-world">Hello World</a><a href="https://wordpress.org/another-day">Another Day</a><a href="https://wordpress.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_valid_links_among_external_links = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://wordpress.org/hello-world">Hello World</a><a href="https://wordpress.org/another-day">Another Day</a><a href="https://wordpress.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a></body></html>';
$html_links_without_duplicate = '<!DOCTYPE html><html class="no-js" lang="en-US"><head><title></title><link href="https://fonts.gstatic.com" crossorigin rel="preconnect" /><link rel="stylesheet" id="wp-block-library-css" href="https://example.org/wp-includes/css/dist/block-library/style.min.css?ver=6.4.3" media="all" /></head><body><button data-link="https://example.org/hello-world">Click Here</button><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/hello-world">Hello World</a><a href="https://example.org/another-day">Another Day</a><a href="https://example.org/rich-dad-poor-dad">Rich Dad Poor Dad</a><a href="https://example.org/rebecca-brown-he-came-to-set-the-captives-free">Buy (He came to set the captives free) - Rebecca Brown</a></body></html>';

return [
    'shouldReturnEmptyWhenNot200' => [
        'config' => [
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
    'shouldReturnEmptyWithNoValidLinks' => [
        'config' => [
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
        'expected' => [],
    ],
    'shouldReturnValidLinksAmongInvalidLinks' => [
        'config' => [
            'headers' => [
                'user-agent' => 'WP Rocket/Pre-fetch Home Links',
                'timeout'    => 60,
            ],
            'found_link' => true,
            'response' => [
                'body'    => $html_valid_links_among_valid_links,
                'response' => [
                    'code'    => 200,
                ],
            ],
        ],
        'expected' => [
            'https://example.org/hello-world',
            'https://example.org/another-day',
            'https://example.org/rich-dad-poor-dad',  
        ],
    ],
    'shouldReturnEmptyWithExternalLinks' => [
        'config' => [
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
        'expected' => [],
    ],
    'shouldReturnValidLinksAmongExternalLinks' => [
        'config' => [
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
        ],
    ],
    'shouldReturnLinksWithoutDuplicate' => [
        'config' => [
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
        ],
    ],
];