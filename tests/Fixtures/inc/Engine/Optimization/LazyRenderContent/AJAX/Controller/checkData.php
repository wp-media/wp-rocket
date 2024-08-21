<?php
return [
	'testShouldBailOutWhenNotAllowed' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'filter'    => false,
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
			'message' => true
		],
	],
	'testShouldReturnSuccess' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'filter'    => true,
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
			'message' => true
		],
	],
	'testShouldReturnError' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'row'       => false,
			'filter'    => true,
		],
		'expected' => [
			'result' => false,
			'message' => false
		],
	],
];
