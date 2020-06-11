<?php

return [

	'testShouldSendJSONSuccess' => [
		'expected' => [
			'response'          => (object) [
				'success' => true,
				'data'    => (object) [
					'process' => 'unsubscribe',
					'message' => 'rocketcdn_disabled',
				],
			],
			'settings'          => [
				'cdn'        => 0,
				'cdn_cnames' => [],
				'cdn_zone'   => [],
			],
			'rocketcdn_process' => false,
			'cron_is_scheduled' => false,
		],
	],
];
