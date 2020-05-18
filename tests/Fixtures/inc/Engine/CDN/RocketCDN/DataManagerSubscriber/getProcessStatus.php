<?php

return [
	'testShouldSendSuccessWhenOptionExists' => [
		'set_rocketcdn_process_option' => true,
		'expected'                     => [
			'response' => (object) [
				'success' => true,
			],
		],
	],

	'testShouldSendErrorWhenOptionNotExists' => [
		'set_rocketcdn_process_option' => null,
		'expected'                     => [
			'response' => (object) [
				'success' => false,
			],
		],
	],
];
