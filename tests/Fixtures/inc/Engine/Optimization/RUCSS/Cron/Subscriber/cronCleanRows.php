<?php

$current_date = 'current_date';
$old_date = 'old_date';


$used_css = [
	[
		'url'            => 'http://example.org/home/',
		'css'            => 'h1{color:red;}',
		'retries'        => 3,
		'is_mobile'      => false,
		'modified'       => $old_date,
		'last_accessed'  => $old_date,
	],
	[
		'url'            => 'http://example.org/category/level1/',
		'css'            => 'h1{color:red;}',
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
				'has_delay' => true,
				'delay' => 1,
				'deletion_activated' => true,
			]
		],
		'shouldNotDeleteOnDisabled' => [
			'input' => [
				'used_css'  => $used_css,
				'has_delay' => true,
				'delay' => 0,
				'deletion_activated' => false,
			]
		]
	],
];
