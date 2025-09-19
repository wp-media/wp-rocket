<?php

return [
	'testShouldShowTrackedPagesWhenPerformanceMonitoringIsDisabled' => [
		'config' => [
			'performance_monitoring' => false,
            'global_score_data' => [
                'score'      => 20,
                'pages_num'  => 2,
                'status'     => 'in-progress',
                'is_running' => true,
            ],
		],
		'expected' => 'Tracked Pages',
	],
	'testShouldShowMonitoredPagesWhenPerformanceMonitoringIsEnabled' => [
		'config' => [
			'performance_monitoring' => true,
            'global_score_data' => [
                'score'      => 20,
                'pages_num'  => 2,
                'status'     => 'in-progress',
                'is_running' => true,
            ],
		],
		'expected' => 'Monitored Pages',
	],
];