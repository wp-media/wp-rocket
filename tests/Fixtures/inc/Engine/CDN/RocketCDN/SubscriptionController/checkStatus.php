<?php
return [
	'taskSuccess' => [
		'config'   => [
			'task_code' => 200,
			'task_body' => [
				'success' => true,
				'status'  => 'SUCCESS',
			],
		],
		'expected' => [
			'pending_transient' => false,
			'cdn_enabled'       => 1,
		],
	],
];
