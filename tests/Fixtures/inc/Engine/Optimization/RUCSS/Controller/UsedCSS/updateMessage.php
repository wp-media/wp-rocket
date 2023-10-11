<?php

return [
	'expectingTrue' => [
		'config'   => [
			'ressources' => [
				'job_id' => 123,
				'code' => 200,
				'message' => 'message',
				'previous_message' => ''
			],
			'result' => true
		],
		'expected' => [
			'ressources' => [
				'job_id' => 123,
				'code' => 200,
				'message' => 'message',
				'previous_message' => '',
				'error_message' => ' - 2023-10-11 20:21:00 200: message'
			],
			'result' => true
		]
	]
];
