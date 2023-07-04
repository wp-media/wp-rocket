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
    'shouldReturnAsExpected' => [
        'config' => [
			'html' => $html,
			'data' => [
				'html' => $html_filtered
			]
        ],
        'expected' => [
			'data' => [
				'html' => $html
			],
			'output' => $html_filtered
        ]
    ],

];
