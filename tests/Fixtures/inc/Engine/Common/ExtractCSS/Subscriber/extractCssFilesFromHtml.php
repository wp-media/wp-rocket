<?php

$html_without_css = file_get_contents(__DIR__ . '/HTML/withoutCSS.php');

$html_with_css = file_get_contents(__DIR__ . '/HTML/withCSS.php');


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

];
