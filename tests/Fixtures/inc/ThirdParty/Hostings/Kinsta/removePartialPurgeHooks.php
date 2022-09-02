<?php
return [
	'shouldRemoveEvents' => [
		'expected' => [
			'actions' => [
				[
					'action' => 'wp_trash_post',
					'callback' => 'rocket_clean_post',
				],
				[
					'action' => 'delete_post',
					'callback' => 'rocket_clean_post',
				],
				[
					'action' => 'clean_post_cache',
					'callback' => 'rocket_clean_post',
				],
				[
					'action' => 'wp_update_comment_count',
					'callback' => 'rocket_clean_post',
				],
			],
			'filters' => [
				[
					'filter' => 'rocket_clean_files',
					'callback' => 'rocket_clean_files_users',
				]
			],
		]
	]
];
