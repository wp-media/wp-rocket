<?php

$input = file_get_contents(__DIR__ . '/HTML/input.html');
$output = file_get_contents(__DIR__ . '/HTML/output.html');
$output_added = file_get_contents(__DIR__ . '/HTML/output_added.html');
return [
    'AddScript' => [
        'config' => [
              'html' => $input,
			  'script' => 'script',
			  'is_allowed' => true,
        ],
        'expected' => $output_added
    ],
	'NotAllowedShouldReturnSame' => [
		'config' => [
			'html' => $input,
			'script' => 'script',
			'is_allowed' => false,
		],
		'expected' => $output
	],
	'NoScriptShould' => [
		'config' => [
			'html' => $input,
			'script' => false,
			'is_allowed' => true,
		],
		'expected' => $output
	],

];
