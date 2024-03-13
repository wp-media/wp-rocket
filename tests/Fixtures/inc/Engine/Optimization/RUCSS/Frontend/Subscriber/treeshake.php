<?php

$html_input = file_get_contents(__DIR__ . '/HTML/input.html');
$html_output = file_get_contents(__DIR__ . '/HTML/output.html');
$html_output_font_excluded = file_get_contents(__DIR__ . '/HTML/outputFontExcluded.html');
$html_output_font_preloaded = file_get_contents(__DIR__ . '/HTML/outputFontPreloaded.html');


return [
	'test_data' => [
		'shouldAddNewRow' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [

				],
				'files' => [

				],
				'font_excluded' => [],
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

				],
				'font_excluded' => [],

			],
			'expected' => [
				'html' => $html_output,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '',
						'queue_name' => '',
						'is_mobile' => false,
						'status'        => 'pending',
						'retries'       => 0,
					]
				],
				'files' => [

				]
			]
		],
		'shouldNotPreloadFontExcluded' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'font_excluded' => ['https://domaina.com'],
				'files' => [
					'wp-content/cache/used-css/1/1/2/3/4abcd.css.gz' => gzencode( file_get_contents(__DIR__ . '/CSS/test.css') )
				],
			],
			'expected' => [
				'html' => $html_output_font_excluded,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'files' => [

				]
			]
		],
		'shouldPreloadFont' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'font_excluded' => [],
				'files' => [
					'wp-content/cache/used-css/1/1/2/3/4abcd.css.gz' => gzencode( file_get_contents(__DIR__ . '/CSS/FontPreloaded.css') )
				],
			],
			'expected' => [
				'html' => $html_output_font_preloaded,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'files' => [

				]
			]
		],
		'shouldPreloadFontEvenWithEmptyExcludeArray' => [
			'config' => [
				'rucss' => true,
				'html' => $html_input,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'font_excluded' => [''],
				'files' => [
					'wp-content/cache/used-css/1/1/2/3/4abcd.css.gz' => gzencode( file_get_contents(__DIR__ . '/CSS/FontPreloaded.css') )
				],
			],
			'expected' => [
				'html' => $html_output_font_preloaded,
				'rows' => [
					[
						'url' => 'http://example.org',
						'job_id' => '1234',
						'queue_name' => 'eu',
						'is_mobile' => false,
						'status'        => 'completed',
						'retries'       => 0,
						'hash'          => '1234abcd',
					]
				],
				'files' => [

				]
			]
		],
	]
];
