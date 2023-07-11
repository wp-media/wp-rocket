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
        ],
        'expected' => [
			'data' => [
				'html' => $html
			],
			'output' => $html_filtered
        ]
    ],

];
