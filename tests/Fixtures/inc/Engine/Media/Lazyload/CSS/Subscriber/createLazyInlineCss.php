<?php

$html = <<<HTML
<html>
<head>
	<link rel="stylesheet" href="/wp-content/rocket-test-data/styles/lazyload_css_background_images.css">
	<link rel="stylesheet" href="https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css">
</head>
<body>
<!-- internal CSS -->
<style>
	body{
		width:40%;
		margin-left: auto;
		margin-right: auto;
	}
	div{
		margin-top: 1em;
		margin-bottom: 1em;
	}
	p {
		font-size: 0.85em;
		color: black;
		background-image: none;
		background-color: transparent;
	}
	.internal-css-background-image{
		width: 100%;
		height: 400px;
		background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
		background-color: #cccccc;
	}
	.internal-css-background-images{
		width: 100%;
		height: 400px;
		background-image: url('https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
		background-color: #cccccc;
	}
</style>
</body>
</html>
HTML;

$html_filtered = <<<HTML
<html>
<head>
	<link rel="stylesheet" href="/wp-content/rocket-test-data/styles/lazyload_css_background_images.css">
	<link rel="stylesheet" href="https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/lazyload_css_background_images.min.css">
</head>
<body>
<!-- internal CSS -->
<style>
	content2</style>
</body>
</html>
HTML;

$css_content = 'body{
		width:40%;
		margin-left: auto;
		margin-right: auto;
	}
	div{
		margin-top: 1em;
		margin-bottom: 1em;
	}
	p {
		font-size: 0.85em;
		color: black;
		background-image: none;
		background-color: transparent;
	}
	.internal-css-background-image{
		width: 100%;
		height: 400px;
		background-image: url("/wp-content/rocket-test-data/images/paper.jpeg");
		background-color: #cccccc;
	}
	.internal-css-background-images{
		width: 100%;
		height: 400px;
		background-image: url(\'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png\'), url( "/wp-content/rocket-test-data/images/paper.jpeg" );
		background-color: #cccccc;
	}
';

return [
    'noHtmlShouldReturnSame' => [
        'config' => [
              'data' => [
				  'css_inline' => [
					  $css_content
			  ],
			],
			'extract' => [],
			'rule_format' => [],
        ],
        'expected' => [
			'css_inline' => [
				$css_content
			]
        ]
    ],
	'noCSSFilesShouldReturnSame' => [
		'config' => [
			'data' => [
				'html' => $html,
				],
			'extract' => [],
			'rule_format' => [],
		],
		'expected' => [
			'html' => $html,
		],
	],
	'shouldReturnAsExpected' => [
		'config' => [
			'data' => [
				'html' => $html,
				'css_inline' => [
					$css_content
				],
			],
			'extract' => [
				$css_content => [
					'selector' => [
						[
							'url' => '/wp-content/rocket-test-data/images/paper.jpeg',
						],
						[
							'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png',
						],
						[
							'url' => '/wp-content/rocket-test-data/images/paper.jpeg',
						],
				]
			],
			'rule_format' => [
				[
					'tag' => [
						'url' => '/wp-content/rocket-test-data/images/paper.jpeg',
						'hash' => 'hash',
					],
					'content' => $css_content,
					'new_content' => 'content',
					'formatted_urls' => [
						'formatted_urls1',
					],
				],
				[
					'tag' => [
						'url' => 'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png',
						'hash' => 'hash',
					],
					'content' => 'content',
					'new_content' => 'content1',
					'formatted_urls' => [
						'formatted_urls2',
					],
				],
				[
					'tag' => [
						'url' => '/wp-content/rocket-test-data/images/paper.jpeg',
						'hash' => 'hash',
					],
					'content' => 'content1',
					'new_content' => 'content2',
					'formatted_urls' => [
						'formatted_urls3',
					],
				],
			],
		],
		'expected' => [
			'html' => $html_filtered,
			'css_inline' => [
				$css_content
			],
			'lazyloaded_images' => [
				[
					'formatted_urls1'
				],
				[
					'formatted_urls2'
				],
				[
					'formatted_urls3'
				]
			]
		]
	]
];
