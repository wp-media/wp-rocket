<?php

$desktop_row = new stdClass();
$desktop_row->url         = 'https://example.com/page';
$desktop_row->score       = 85;
$desktop_row->status      = 'completed';
$desktop_row->modified    = '2026-03-20 10:00:00';
$desktop_row->report_url  = 'https://gtmetrix.com/reports/page-desktop';
$desktop_row->metric_data = null;
$desktop_row->is_mobile   = false;
$desktop_row->title       = 'Page Title';

$mobile_row = new stdClass();
$mobile_row->url         = 'https://example.com/page';
$mobile_row->score       = 75;
$mobile_row->status      = 'completed';
$mobile_row->modified    = '2026-03-20 10:00:00';
$mobile_row->report_url  = 'https://gtmetrix.com/reports/page-mobile';
$mobile_row->metric_data = '{"lcp":1200}';
$mobile_row->is_mobile   = true;
$mobile_row->title       = 'Page Title';

return [
	'testShouldReturnExistsTrueWithDesktopRow' => [
		'config'   => [
			'input'       => [ 'url' => 'https://example.com/page' ],
			'rows'        => [ $desktop_row ],
			'max_urls'    => null,
			'total_count' => null,
		],
		'expected' => [
			'queried_url' => 'https://example.com/page',
			'result'      => [
				'exists'  => true,
				'results' => [
					[
						'url'         => 'https://example.com/page',
						'score'       => 85,
						'status'      => 'completed',
						'modified'    => '2026-03-20 10:00:00',
						'report_url'  => 'https://gtmetrix.com/reports/page-desktop',
						'metric_data' => null,
						'is_mobile'   => false,
						'title'       => 'Page Title',
					],
				],
			],
		],
	],
	'testShouldReturnExistsTrueWithDesktopAndMobileRows' => [
		'config'   => [
			'input'       => [ 'url' => 'https://example.com/page' ],
			'rows'        => [ $desktop_row, $mobile_row ],
			'max_urls'    => null,
			'total_count' => null,
		],
		'expected' => [
			'queried_url' => 'https://example.com/page',
			'result'      => [
				'exists'  => true,
				'results' => [
					[
						'url'         => 'https://example.com/page',
						'score'       => 85,
						'status'      => 'completed',
						'modified'    => '2026-03-20 10:00:00',
						'report_url'  => 'https://gtmetrix.com/reports/page-desktop',
						'metric_data' => null,
						'is_mobile'   => false,
						'title'       => 'Page Title',
					],
					[
						'url'         => 'https://example.com/page',
						'score'       => 75,
						'status'      => 'completed',
						'modified'    => '2026-03-20 10:00:00',
						'report_url'  => 'https://gtmetrix.com/reports/page-mobile',
						'metric_data' => '{"lcp":1200}',
						'is_mobile'   => true,
						'title'       => 'Page Title',
					],
				],
			],
		],
	],
	'testShouldReturnExistsFalseWithFreeSlots' => [
		'config'   => [
			'input'       => [ 'url' => 'https://example.com/not-monitored' ],
			'rows'        => false,
			'max_urls'    => 10,
			'total_count' => 7,
		],
		'expected' => [
			'queried_url' => 'https://example.com/not-monitored',
			'result'      => [
				'exists'     => false,
				'free_slots' => 3,
			],
		],
	],
	'testShouldReturnExistsFalseWithZeroFreeSlotsWhenAtCapacity' => [
		'config'   => [
			'input'       => [ 'url' => 'https://example.com/not-monitored' ],
			'rows'        => false,
			'max_urls'    => 5,
			'total_count' => 5,
		],
		'expected' => [
			'queried_url' => 'https://example.com/not-monitored',
			'result'      => [
				'exists'     => false,
				'free_slots' => 0,
			],
		],
	],
	'testShouldReturnExistsFalseWithZeroFreeSlotsWhenOverCapacity' => [
		'config'   => [
			'input'       => [ 'url' => 'https://example.com/not-monitored' ],
			'rows'        => false,
			'max_urls'    => 3,
			'total_count' => 5,
		],
		'expected' => [
			'queried_url' => 'https://example.com/not-monitored',
			'result'      => [
				'exists'     => false,
				'free_slots' => 0,
			],
		],
	],
];
