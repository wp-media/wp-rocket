<?php

return [
	'shouldBehaveNormal' => [
		'config' => [
			'row_details' => (object) [
				'id' => 1,
				'retries' => 0,
				'status' => 'in-progress',
			],
			'job_details' => [
				'code' => 404,
			],
		],
		'expected' => []
	],
];
