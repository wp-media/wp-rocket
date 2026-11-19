<?php

return [
	'test_data' => [
		'shouldBailWhenLoadingInSameLanguage'    => [
			'config'   => [
				'transient_data' => [
					'status'          => 'loading',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'language'        => 'fr',
				],
			],
			'expected' => [ 'bails' => true ],
		],

		'shouldBailWhenLoadingInAnotherLanguage' => [
			'config'   => [
				'transient_data' => [
					'status'          => 'loading',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'language'        => 'en',
				],
			],
			'expected' => [ 'bails' => true ],
		],

		'shouldContinueWhenNotLoading'           => [
			'config'   => [
				'transient_data' => [
					'status'          => 'completed',
					'recommendations' => [],
					'metadata'        => [],
					'timestamp'       => 1234567890,
					'language'        => 'en',
				],
			],
			'expected' => [ 'bails' => false ],
		],
	],
];
