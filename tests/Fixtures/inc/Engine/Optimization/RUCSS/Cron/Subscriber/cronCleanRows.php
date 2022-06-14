<?php

$current_date = current_time( 'mysql', true );
$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 32 days' ) );

$used_css = [
	[
		'url'            => 'http://example.org/home/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

return [
	'test_data' => [
		'shouldDeleteOnUpdate' => [
			'input' => [
				'used_css' => $used_css,
			]
		],
	],
];
