<?php

$html = <<<HTML
<html>
	<head></head>
	<body></body>
</html>
HTML;

$html_filtered = <<<HTML
<html>
	<head><tags/></head>
	<body></body>
</html>
HTML;


return [
    'noHTMLShouldReturnSame' => [
        'config' => [
			'data' => [
				'lazyloaded_images' => [
					[
						'selector' => '#id',
						'style' => ':root {}'
					]
				]
			],
			'load_filtered' => [
				'filtered'
			],
			'tags' => '<tags/>'
        ],
        'expected' => [
			'output' => [
				'lazyloaded_images' => [
					[
						'selector' => '#id',
						'style' => ':root {}'
					]
				]
			],
			'lazyloaded_images' => [
				[
					'selector' => '#id',
					'style' => ':root {}'
				]
			],
			'loaded' => [
				'filtered'
			]
        ]
    ],
	'noLazyloadedImagesShouldReturnSame' => [
		'config' => [
			'data' => [
				'html' => $html,
			],
			'load_filtered' => [
				'filtered'
			],
			'tags' => '<tags/>'
		],
		'expected' => [
				'output' => [
					'html' => $html,
				],
				'lazyloaded_images' => [
					[
						'selector' => '#id',
						'style' => ':root {}'
					]
				],
				'loaded' => [
					'filtered'
				]
			]
	],
	'shouldReturnAsExpected' => [
		'config' => [
			'data' => [
				'html' => $html,
				'lazyloaded_images' => [
					[
						'selector' => '#id',
						'style' => ':root {}'
					]
				]
			],
			'load_filtered' => [
				'filtered'
			],
			'tags' => '<tags/>'
		],
		'expected' => [
			'output' => [
				'html' => $html_filtered,
				'lazyloaded_images' => [
					[
						'selector' => '#id',
						'style' => ':root {}'
					]
				]
			],
			'lazyloaded_images' => [
				[
					'selector' => '#id',
					'style' => ':root {}'
				]
			],
			'loaded' => [
				'filtered'
			]
		]
	]
];
