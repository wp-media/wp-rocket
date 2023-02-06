<?php
if(function_exists('current_time')) {
	$current_date = current_time( 'mysql', true );
	$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 32 days' ) );
} else {
	$current_date = 'current_date';
	$old_date = 'old_date';
}

$used_css = [
	[
		'url'            => 'http://example.org/home',
		'css'            => 'h1{color:red;}',
        'hash'           => '',
        'error_code'     => '400',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
        'job_id'         => 304732178,
        'status'         => 'failed',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1',
		'css'            => 'h1{color:red;}',
        'hash'           => '',
        'error_code'     => '400',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
        'job_id'         => 969832401,
        'status'         => 'failed',
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

return [
    'shouldDeleteFailedJobs' => [
        'input' => [
            'used_css' => $used_css,
        ],
        'expected' => [],
    ],
];
