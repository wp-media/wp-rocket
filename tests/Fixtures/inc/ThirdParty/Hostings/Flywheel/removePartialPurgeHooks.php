<?php
return [
	'removeActions' => [
		'config' => [],
		'expected' => [
			'actions' => [
				'rocket_clean_post' => [
					'wp_trash_post',
					'delete_post',
					'clean_post_cache',
					'wp_update_comment_count'
				],
			],
			'filters' => [
				'rocket_clean_files_users' => [
					'rocket_clean_files',
				]
			]
		]
	]
];
