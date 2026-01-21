<?php
return [
	'testShouldBailOutWhenNotAllowed' => [
		'config'   => [
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'filter'    => false,
			'row'       => (object) [
				'domains' => json_encode( [
					(object) [
						'http://exampled-domain.ng/',
						'http://exampled-domain.com',
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
				'domains' => json_encode( [
					(object) [
						'http://exampled-domain.ng/',
						'http://exampled-domain.com',
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
