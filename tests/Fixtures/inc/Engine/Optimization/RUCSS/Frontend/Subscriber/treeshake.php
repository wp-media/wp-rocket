<?php

$html_input = file_get_contents(__DIR__ . '/HTML/input.html');
$html_output = file_get_contents(__DIR__ . '/HTML/output.html');


return [
	'test_data' => [
		'shouldAddNewRow' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [

				],
				'files' => [

				]

			],
			'expected' => [
				'html' => $html_output,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '',
						'queue_name' => '',
						'is_mobile' => false,
						'status'        => 'to-submit',
						'retries'       => 0,
					]
				],
				'files' => [

				]
			]
		],
		'ExistingRowShouldNotReset' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '',
						'queue_name' => '',
						'is_mobile' => false,
						'status'        => 'pending',
						'retries'       => 3,
					]
				],
				'files' => [

				]

			],
			'expected' => [
				'html' => $html_output,
				'rows' => [
					[
						'url' => 'http://example.org',
						'is_mobile' => false,
						'status'        => 'pending',
						'retries'       => 0,
					]
				],
				'files' => [

				]
			]
		],
	]
];
