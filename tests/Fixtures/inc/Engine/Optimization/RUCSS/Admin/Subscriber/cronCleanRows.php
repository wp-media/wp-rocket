<?php

$current_date = current_time( 'mysql', true );
$old_date     = date('Y-m-d H:i:s', strtotime( $current_date. ' - 1 month' ) );

$used_css = [
	[
		'url'            => 'http://example.org/path1',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/path2',
		'css'            => 'h1{color:red;}',
		'unprocessedcss' => wp_json_encode( [] ),
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
	'shouldNotDeleteOnUpdateDueToMissingSettings' => [
		'input' => [
			'remove_unused_css' => false,
			'used_css'          => $used_css,
			'resources'         => $resources,
		]
	],
	'shouldDeleteOnUpdate' => [
		'input' => [
			'remove_unused_css' => true,
			'used_css'          => $used_css,
			'resources'         => $resources,
		]
	],
];
