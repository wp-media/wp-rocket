<?php

$html = <<<HTML
<html>
	<head></head>
	<body></body>
</html>
HTML;

$html_filtered = <<<HTML
<html>
	<head></head>
</html>
HTML;


return [
	'notAllowedShouldReturnSame' => [
		'config' => [
			'html' => $html,
			'data' => [
				'html' => $html_filtered
			],
			'is_allowed' => false,
			'hash_mapping' => [
				'http://example.org/wp-content/rocket-test-data/images/test.png' => '15ef8',
				'http://example.org/test.png' => '16ef9',
				'https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg' => '17ef10',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg' => '18ef11',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/./wp-content/rocket-test-data/images/test.png' => '19ef12',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/../rocket-test-data/images/papertest.jpeg' => '20ef13',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/papertest.jpeg' => '21ef14',
				'http://example.org/wp-content/rocket-test-data/images/testnotExist.png' => '22ef15',
				'http://example.org/wp-content/rocket-test-data/images/butterfly.avif' => '23ef16',
				'http://example.org/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff' => '24ef17',
				'http://example.org/wp-content/rocket-test-data/images/paper.jpeg' => '25ef18',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png' => '26ef19',
			],
		],
		'expected' => [
			'data' => [
				'html' => $html
			],
			'output' => $html
		]
	],
    'shouldReturnAsExpected' => [
        'config' => [
			'html' => $html,
			'data' => [
				'html' => $html_filtered
			],
			'is_allowed' => true,
			'hash_mapping' => [
				'http://example.org/wp-content/rocket-test-data/images/test.png' => '15ef8',
				'http://example.org/test.png' => '16ef9',
				'https://upload.wikimedia.org/wikipedia/commons/1/11/Test-Logo.svg' => '17ef10',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/paper.jpeg' => '18ef11',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/./wp-content/rocket-test-data/images/test.png' => '19ef12',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/styles/../rocket-test-data/images/papertest.jpeg' => '20ef13',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/papertest.jpeg' => '21ef14',
				'http://example.org/wp-content/rocket-test-data/images/testnotExist.png' => '22ef15',
				'http://example.org/wp-content/rocket-test-data/images/butterfly.avif' => '23ef16',
				'http://example.org/wp-content/rocket-test-data/images/file_example_TIFF_1MB.tiff' => '24ef17',
				'http://example.org/wp-content/rocket-test-data/images/paper.jpeg' => '25ef18',
				'https://new.rocketlabsqa.ovh/wp-content/rocket-test-data/images/test.png' => '26ef19',
			],
        ],
        'expected' => [
			'data' => [
				'html' => $html
			],
			'output' => $html_filtered
        ]
    ],

];
