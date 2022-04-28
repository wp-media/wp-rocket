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
		'url'            => 'http://example.org/home/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1/',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
];

$resources = [
	[
		'url'           => 'http://example.org/wp-content/themes/theme-name/style.css',
		'content'       => '.theme-name{color:red;}',
		'type'          => 'css',
		'media'         => 'all',
		'modified'      => $old_date,
		'last_accessed' => $old_date,
	],
	[
		'url'           => 'http://example.org/css/style.css',
		'content'       => '.first{color:green;}',
		'type'          => 'css',
		'media'         => 'all',
		'modified'      => $current_date,
		'last_accessed' => $current_date,
	]
];

return [
	'test_data' => [
		'shouldNotDeleteOnUpdateDueToMissingSettings' => [
			'input' => [
				'remove_unused_css'      => false,
				'used_css'               => $used_css,
				'resources'              => $resources,
			]
		],
		'shouldDeleteOnUpdate' => [
			'input' => [
				'remove_unused_css' => true,
				'used_css'          => $used_css,
				'resources'         => $resources,
				'has_delay' => true,
				'delay' => 1,
				'deletion_activated' => true,
			]
		],
		'shouldNotDeleteOnDisabled' => [
			'input' => [
				'remove_unused_css' => true,
				'used_css'               => $used_css,
				'resources'         => $resources,
				'has_delay' => true,
				'delay' => 0
			]
		]
	],
];
