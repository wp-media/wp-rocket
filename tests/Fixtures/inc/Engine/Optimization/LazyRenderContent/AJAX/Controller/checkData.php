<?php
return [
	'testShouldReturnSuccess' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'row'       => (object) [
				'below_the_fold' => json_encode( [
					(object) [
						'db47c7d69edcf4565baa182deb470091',
						'db47c7d69edcf4565baa182deb470092',
					],
				] ),
			],
		],
		'expected' => [
			'result' => true,
			'message' => 'data already exists'
		],
	],
	'testShouldReturnError' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'row'       => false,
		],
		'expected' => [
			'result' => false,
			'message' => 'data does not exist'
		],
	],
];
