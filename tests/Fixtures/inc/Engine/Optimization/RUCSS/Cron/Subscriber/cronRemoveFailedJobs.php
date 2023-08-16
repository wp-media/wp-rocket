<?php
if(function_exists('current_time')) {
	$current_date = current_time( 'mysql', true );
	$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 3 days' ) );
} else {
	$current_date = 'current_date';
	$old_date = 'old_date';
}

$failed_used_css = [
    [
		'url'            => 'http://example.org/hello-world',
        'hash'           => '',
        'error_code'     => '400',
		'retries'        => 3,
		'is_mobile'      => false,
        'job_id'         => 403192106,
        'status'         => 'failed',
		'modified'       => date('Y-m-d H:i:s'),
		'last_accessed'  => date('Y-m-d H:i:s'),
	],
	[
		'url'            => 'http://example.org/home',
        'hash'           => '',
        'error_code'     => '400',
		'retries'        => 3,
		'is_mobile'      => false,
        'job_id'         => 304732178,
        'status'         => 'failed',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1',
        'hash'           => '',
        'error_code'     => '400',
		'retries'        => 3,
		'is_mobile'      => false,
        'job_id'         => 969832401,
        'status'         => 'failed',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

$pending_used_css = [
	[
		'url'            => 'http://example.org/home',
        'hash'           => '',
        'error_code'     => '',
		'retries'        => 0,
		'is_mobile'      => false,
        'job_id'         => 304732178,
        'status'         => 'pending',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1',
        'hash'           => '',
        'error_code'     => '',
		'retries'        => 0,
		'is_mobile'      => false,
        'job_id'         => 969832401,
        'status'         => 'pending',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

return [
    'shouldDeleteFailedJobs' => [
        'input' => [
            'used_css' => $failed_used_css,
			'add_job_to_queue_response'=> [
				'headers'=>[],
				'response' => array('code' => 200),
				'body'=>'{"code": 200,
				"message": "Added to Queue successfully.",
				"contents": {
					"jobId": "OVH_EU--496540278",
					"queueName": "EU",
					"isHome": false,
					"queueFullName": "rucssJob_EU"
					}
				}'
			]
        ],
        'expected' => $pending_used_css,
    ],
	'shouldNotDeleteFailedJobsNoResponse' => [
		'input' => [
			'used_css' => $failed_used_css,
			'add_job_to_queue_response'=> [
				'headers'=>[],
				'response' => array('code' => 404,'message'=> "error."),
				'body'=>''
			]
		],
		'expected' => [],
	],
];
