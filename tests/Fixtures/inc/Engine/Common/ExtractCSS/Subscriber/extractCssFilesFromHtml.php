<?php

$html_without_css = file_get_contents(__DIR__ . '/HTML/withoutCSS.php');

$html_with_css = file_get_contents(__DIR__ . '/HTML/withCSS.php');

$html_rel_text_in_href = <<<'HTML'
<html><head>
<link rel="preconnect" href="https://external.test/a/b.css#rel=stylesheet">
<link rel="preconnect" href="https://external.test/a.css?rel=preload&as=style">
<link rel="dns-prefetch" href="https://external.test/x.css#rel='stylesheet'">
<link rel="prefetch" href="https://external.test/c.css?x=rel=stylesheet">
</head></html>
HTML;

$html_rel_text_in_other_attributes = <<<'HTML'
<html><head>
<link rel="icon" href="/a.css" title="rel=stylesheet">
<link data-rel="stylesheet" rel="preload" as="image" href="/b.css">
<link rel="preload" as="font" href="/c.css" onload="this.rel='stylesheet'">
<link rel="preload" href="/d.css" onload="this.rel='stylesheet'">
<link rel="icon" data-as="style" title="rel=preload" href="/e.css">
<link data-x=a=rel=stylesheet rel=preconnect href="https://external.test/a.css">
</head></html>
HTML;

$html_stylesheet_variants = <<<'HTML'
<html><head>
<link rel="stylesheet" href="/v1.css">
<link rel='stylesheet' href='/v2.css'>
<link rel=stylesheet href="/v3.css">
<link REL="StyleSheet" HREF="/v4.css">
<link rel = "stylesheet" href="/v5.css">
<link rel=" stylesheet " href="/v6.css">
<link
	href="https://example.org/v7.css"
	rel="stylesheet"
	media="all"
>
<link href="/v8.css" rel="stylesheet" />
<link href="/v9.css" rel=stylesheet/>
<link href="/v10.css"rel="stylesheet">
<link rel="stylesheet" media="print" onload="this.media='all'" href="/v11.css">
<link rel="stylesheet" href="/v12.css" title="a>b">
</head></html>
HTML;

$html_preload_as_style = <<<'HTML'
<html><head>
<link rel="preload" as="style" href="/p1.css">
<link rel=preload as=style href="/p2.css">
<link rel="preload" href="/p3.css" as="Style" onload="this.onload=null;this.rel='stylesheet'">
<link rel="preload" as="font" href="/p4.css" crossorigin>
</head></html>
HTML;

$html_rel_token_list = <<<'HTML'
<html><head>
<link rel="preload stylesheet" href="/r1.css">
<link rel="prefetch stylesheet" href="/r2.css">
<link rel="stylesheet/" href="/r3.css">
</head></html>
HTML;

$html_alternate_stylesheet = <<<'HTML'
<html><head>
<link rel="alternate stylesheet" href="/alt1.css">
<link rel="stylesheet alternate" href="/alt2.css">
<link rel="stylesheet" href="/main.css">
</head></html>
HTML;

$html_duplicate_rel = <<<'HTML'
<html><head>
<link rel="prefetch" rel="stylesheet" href="/f1.css">
<link rel="stylesheet" rel="preload" href="/f2.css">
</head></html>
HTML;

$html_missing_rel = <<<'HTML'
<html><head>
<link rel="" href="/m1.css">
<link href="/m2.css">
<link rel="&#115;tylesheet" href="/m3.css">
</head></html>
HTML;

$html_google_fonts = <<<'HTML'
<html><head>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto&display=swap">
<link rel="stylesheet" href="/g.css">
</head></html>
HTML;

return [
	'noDataShouldReturnSame' => [
		'config' => [
			'data' => [
			],
		],
		'expected' => [

		]
	],
    'noCssShouldReturnEmpty' => [
        'config' => [
              'data' => [
				  'html' => $html_without_css
			  ],

        ],
        'expected' => [
			'html' => $html_without_css,
			"css_files" =>  []
        ]
    ],
	'cssShouldReturnUtrls' => [
		'config' => [
			'data' => [
				'html' => $html_with_css
			],
		],
		'expected' => [
			'html' => $html_with_css,
			'css_files' => [
				'/wp-content/rocket-test-data/styles/lazyload_css_background_images.css',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css',
			]
		]
	],
	'shouldSkipNonStylesheetLinkWithRelTextInHref' => [
		'config' => [
			'data' => [
				'html' => $html_rel_text_in_href,
			],
		],
		'expected' => [
			'html' => $html_rel_text_in_href,
			'css_files' => [],
		],
	],
	'shouldSkipLinkWithRelTextInOtherAttributes' => [
		'config' => [
			'data' => [
				'html' => $html_rel_text_in_other_attributes,
			],
		],
		'expected' => [
			'html' => $html_rel_text_in_other_attributes,
			'css_files' => [],
		],
	],
	'shouldExtractStylesheetTagVariants' => [
		'config' => [
			'data' => [
				'html' => $html_stylesheet_variants,
			],
		],
		'expected' => [
			'html' => $html_stylesheet_variants,
			'css_files' => [
				'/v1.css',
				'/v2.css',
				'/v3.css',
				'/v4.css',
				'/v5.css',
				'/v6.css',
				'https://example.org/v7.css',
				'/v8.css',
				'/v9.css',
				'/v10.css',
				'/v11.css',
				'/v12.css',
			],
		],
	],
	'shouldExtractPreloadAsStyle' => [
		'config' => [
			'data' => [
				'html' => $html_preload_as_style,
			],
		],
		'expected' => [
			'html' => $html_preload_as_style,
			'css_files' => [
				'/p1.css',
				'/p2.css',
				'/p3.css',
			],
		],
	],
	'shouldExtractStylesheetFromRelTokenList' => [
		'config' => [
			'data' => [
				'html' => $html_rel_token_list,
			],
		],
		'expected' => [
			'html' => $html_rel_token_list,
			'css_files' => [
				'/r1.css',
				'/r2.css',
			],
		],
	],
	'shouldSkipAlternateStylesheet' => [
		'config' => [
			'data' => [
				'html' => $html_alternate_stylesheet,
			],
		],
		'expected' => [
			'html' => $html_alternate_stylesheet,
			'css_files' => [
				'/main.css',
			],
		],
	],
	'shouldUseFirstRelAttribute' => [
		'config' => [
			'data' => [
				'html' => $html_duplicate_rel,
			],
		],
		'expected' => [
			'html' => $html_duplicate_rel,
			'css_files' => [
				'/f2.css',
			],
		],
	],
	'shouldSkipLinkWithoutStylesheetRel' => [
		'config' => [
			'data' => [
				'html' => $html_missing_rel,
			],
		],
		'expected' => [
			'html' => $html_missing_rel,
			'css_files' => [],
		],
	],
	'shouldKeepGoogleFontsExcluded' => [
		'config' => [
			'data' => [
				'html' => $html_google_fonts,
			],
		],
		'expected' => [
			'html' => $html_google_fonts,
			'css_files' => [
				'/g.css',
			],
		],
	],
];
