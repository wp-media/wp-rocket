<?php

return [
	'test_data' => [
		'shouldInsertWhenNoRowExists'                            => [
			'config'   => [
				'race' => false,
			],
			'expected' => [
				'status' => 'to-submit',
				'job_id' => '',
			],
		],
		'shouldReturnTheWinnerRowUntouchedWhenTheInsertLosesTheRace' => [
			'config'   => [
				'race' => true,
			],
			'expected' => [
				'status' => 'pending',
				'job_id' => 'W',
			],
		],
	],
];
