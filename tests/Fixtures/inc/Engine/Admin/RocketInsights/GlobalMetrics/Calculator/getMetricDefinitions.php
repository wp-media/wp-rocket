<?php

return [
	'test_data' => [
		'shouldReturnLabelAndTooltipOfEachMetricInDisplayOrder' => [
			'expected' => [
				'largest_contentful_paint' => [
					'label'   => 'LCP',
					'tooltip' => 'Time until the largest visible content element renders and the main content becomes visible.',
				],
				'total_blocking_time'      => [
					'label'   => 'TBT',
					'tooltip' => 'Total time the main thread is blocked before the page becomes interactive during loading.',
				],
				'cumulative_layout_shift'  => [
					'label'   => 'CLS',
					'tooltip' => 'Total amount of unexpected layout shifts during page loading, affecting visual stability.',
				],
				'time_to_first_byte'       => [
					'label'   => 'TTFB',
					'tooltip' => 'Time from the request until the server responds, determining how soon the page starts loading.',
				],
			],
		],
	],
];
